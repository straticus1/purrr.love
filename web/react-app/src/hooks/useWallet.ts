import { useAccount, useConnect, useDisconnect, useNetwork, useSwitchNetwork } from 'wagmi';
import { useCallback, useEffect } from 'react';
import { SUPPORTED_CHAIN_IDS, getNetworkConfig, shortenAddress } from '@/lib/web3';
import toast from 'react-hot-toast';

export const useWallet = () => {
  const { address, isConnected, isConnecting } = useAccount();
  const { connect, connectors, error: connectError, isLoading: isConnectLoading, pendingConnector } = useConnect();
  const { disconnect } = useDisconnect();
  const { chain } = useNetwork();
  const { switchNetwork, isLoading: isSwitchingNetwork } = useSwitchNetwork();

  // Check if current network is supported
  const isNetworkSupported = chain ? SUPPORTED_CHAIN_IDS.includes(chain.id as any) : false;
  const networkConfig = chain ? getNetworkConfig(chain.id) : null;

  // Handle connection errors
  useEffect(() => {
    if (connectError) {
      toast.error(`Connection failed: ${connectError.message}`);
    }
  }, [connectError]);

  // Connect to wallet
  const connectWallet = useCallback(async (connectorId?: string) => {
    try {
      const connector = connectorId 
        ? connectors.find(c => c.id === connectorId) 
        : connectors[0]; // Default to first connector (MetaMask)
      
      if (!connector) {
        toast.error('Wallet connector not found');
        return;
      }

      connect({ connector });
    } catch (error) {
      console.error('Failed to connect wallet:', error);
      toast.error('Failed to connect wallet');
    }
  }, [connect, connectors]);

  // Disconnect wallet
  const disconnectWallet = useCallback(() => {
    try {
      disconnect();
      toast.success('Wallet disconnected');
    } catch (error) {
      console.error('Failed to disconnect wallet:', error);
      toast.error('Failed to disconnect wallet');
    }
  }, [disconnect]);

  // Switch to supported network
  const switchToSupportedNetwork = useCallback(async (chainId?: number) => {
    if (!switchNetwork) {
      toast.error('Network switching not supported by your wallet');
      return;
    }

    const targetChainId = chainId || SUPPORTED_CHAIN_IDS[0]; // Default to mainnet
    
    try {
      await switchNetwork(targetChainId);
      toast.success(`Switched to ${getNetworkConfig(targetChainId)?.name}`);
    } catch (error: any) {
      console.error('Failed to switch network:', error);
      
      // Handle specific error cases
      if (error.code === 4902) {
        toast.error('Please add this network to your wallet first');
      } else if (error.code === 4001) {
        toast.error('Network switch rejected by user');
      } else {
        toast.error('Failed to switch network');
      }
    }
  }, [switchNetwork]);

  // Format address for display
  const formattedAddress = address ? shortenAddress(address) : '';

  // Get available connectors with metadata
  const availableConnectors = connectors.map(connector => ({
    id: connector.id,
    name: connector.name,
    ready: connector.ready,
    icon: getConnectorIcon(connector.id),
  }));

  return {
    // Connection state
    address,
    formattedAddress,
    isConnected,
    isConnecting: isConnecting || isConnectLoading,
    
    // Network state
    chain,
    networkConfig,
    isNetworkSupported,
    isSwitchingNetwork,
    
    // Available connectors
    connectors: availableConnectors,
    pendingConnector,
    
    // Actions
    connectWallet,
    disconnectWallet,
    switchToSupportedNetwork,
    
    // Computed values
    canInteract: isConnected && isNetworkSupported,
    needsNetworkSwitch: isConnected && !isNetworkSupported,
  };
};

// Helper function to get connector icons
function getConnectorIcon(connectorId: string): string {
  const icons: Record<string, string> = {
    metaMask: '🦊',
    walletConnect: '🔗',
    injected: '👛',
    coinbaseWallet: '🔵',
  };
  
  return icons[connectorId] || '👛';
}