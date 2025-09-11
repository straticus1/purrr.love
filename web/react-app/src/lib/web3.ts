import { createConfig, configureChains } from 'wagmi';
import { mainnet, polygon, arbitrum, optimism, goerli } from 'wagmi/chains';
import { MetaMaskConnector } from 'wagmi/connectors/metaMask';
import { WalletConnectConnector } from 'wagmi/connectors/walletConnect';
import { InjectedConnector } from 'wagmi/connectors/injected';
import { publicProvider } from 'wagmi/providers/public';
import { alchemyProvider } from 'wagmi/providers/alchemy';
import { infuraProvider } from 'wagmi/providers/infura';

// Environment variables
const alchemyApiKey = import.meta.env.VITE_ALCHEMY_API_KEY;
const infuraApiKey = import.meta.env.VITE_INFURA_API_KEY;
const walletConnectProjectId = import.meta.env.VITE_WALLETCONNECT_PROJECT_ID;

// Configure chains and providers
const { chains, publicClient, webSocketPublicClient } = configureChains(
  [
    mainnet,
    polygon,
    arbitrum,
    optimism,
    ...(import.meta.env.DEV ? [goerli] : []),
  ],
  [
    // Primary providers
    ...(alchemyApiKey ? [alchemyProvider({ apiKey: alchemyApiKey })] : []),
    ...(infuraApiKey ? [infuraProvider({ apiKey: infuraApiKey })] : []),
    // Fallback to public provider
    publicProvider(),
  ]
);

// Configure connectors
const connectors = [
  new MetaMaskConnector({
    chains,
    options: {
      shimDisconnect: true,
    },
  }),
  new WalletConnectConnector({
    chains,
    options: {
      projectId: walletConnectProjectId || 'your-project-id',
      metadata: {
        name: 'Purrr.love',
        description: 'The Ultimate Cat Gaming Ecosystem with NFTs',
        url: 'https://purrr.love',
        icons: ['https://purrr.love/favicon.ico'],
      },
    },
  }),
  new InjectedConnector({
    chains,
    options: {
      name: 'Injected Wallet',
      shimDisconnect: true,
    },
  }),
];

// Create wagmi config
export const config = createConfig({
  autoConnect: true,
  connectors,
  publicClient,
  webSocketPublicClient,
});

export { chains };

// Contract addresses (update with actual deployed contracts)
export const CONTRACTS = {
  // Ethereum mainnet
  1: {
    CAT_NFT: '0x...',
    MARKETPLACE: '0x...',
    BREEDING: '0x...',
    TOKEN: '0x...',
  },
  // Polygon
  137: {
    CAT_NFT: '0x...',
    MARKETPLACE: '0x...',
    BREEDING: '0x...',
    TOKEN: '0x...',
  },
  // Goerli testnet
  5: {
    CAT_NFT: '0x...',
    MARKETPLACE: '0x...',
    BREEDING: '0x...',
    TOKEN: '0x...',
  },
} as const;

// Supported networks for the application
export const SUPPORTED_CHAIN_IDS = [1, 137, 5] as const;

// Network configurations
export const NETWORK_CONFIG = {
  1: {
    name: 'Ethereum Mainnet',
    currency: 'ETH',
    explorerUrl: 'https://etherscan.io',
    rpcUrl: `https://eth-mainnet.g.alchemy.com/v2/${alchemyApiKey}`,
  },
  137: {
    name: 'Polygon',
    currency: 'MATIC',
    explorerUrl: 'https://polygonscan.com',
    rpcUrl: `https://polygon-mainnet.g.alchemy.com/v2/${alchemyApiKey}`,
  },
  5: {
    name: 'Goerli Testnet',
    currency: 'ETH',
    explorerUrl: 'https://goerli.etherscan.io',
    rpcUrl: `https://eth-goerli.g.alchemy.com/v2/${alchemyApiKey}`,
  },
} as const;

// Utility functions
export const getNetworkConfig = (chainId: number) => {
  return NETWORK_CONFIG[chainId as keyof typeof NETWORK_CONFIG];
};

export const getContractAddress = (chainId: number, contract: string) => {
  const chainContracts = CONTRACTS[chainId as keyof typeof CONTRACTS];
  return chainContracts?.[contract as keyof typeof chainContracts];
};

export const shortenAddress = (address: string, chars = 4) => {
  if (!address) return '';
  return `${address.slice(0, chars + 2)}...${address.slice(-chars)}`;
};

export const formatTokenAmount = (amount: string | number, decimals = 18) => {
  const num = typeof amount === 'string' ? parseFloat(amount) : amount;
  return (num / Math.pow(10, decimals)).toFixed(4);
};