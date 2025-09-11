import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Wallet, Power, AlertTriangle, CheckCircle, ExternalLink } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { useWallet } from '@/hooks/useWallet';

interface WalletConnectionProps {
  onConnect?: () => void;
  showBalance?: boolean;
  compact?: boolean;
}

export const WalletConnection: React.FC<WalletConnectionProps> = ({
  onConnect,
  showBalance = false,
  compact = false,
}) => {
  const {
    address,
    formattedAddress,
    isConnected,
    isConnecting,
    chain,
    networkConfig,
    isNetworkSupported,
    isSwitchingNetwork,
    connectors,
    connectWallet,
    disconnectWallet,
    switchToSupportedNetwork,
    needsNetworkSwitch,
  } = useWallet();

  const handleConnect = async (connectorId: string) => {
    await connectWallet(connectorId);
    onConnect?.();
  };

  const handleNetworkSwitch = () => {
    switchToSupportedNetwork(1); // Switch to mainnet by default
  };

  if (compact) {
    return (
      <div className="flex items-center gap-2">
        {isConnected ? (
          <>
            <div className="flex items-center gap-2 px-3 py-2 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm">
              <CheckCircle className="w-4 h-4" />
              <span>{formattedAddress}</span>
            </div>
            {needsNetworkSwitch && (
              <Button
                onClick={handleNetworkSwitch}
                loading={isSwitchingNetwork}
                variant="outline"
                size="sm"
                className="text-orange-600 hover:text-orange-700 border-orange-200"
              >
                Switch Network
              </Button>
            )}
          </>
        ) : (
          <Button
            onClick={() => connectWallet()}
            loading={isConnecting}
            variant="primary"
            size="sm"
            icon={<Wallet className="w-4 h-4" />}
          >
            Connect Wallet
          </Button>
        )}
      </div>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Wallet className="w-5 h-5" />
          Web3 Wallet
        </CardTitle>
      </CardHeader>

      <CardContent className="space-y-4">
        <AnimatePresence mode="wait">
          {!isConnected ? (
            <motion.div
              key="disconnected"
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -20 }}
              className="space-y-4"
            >
              <p className="text-gray-600 dark:text-gray-400 text-sm">
                Connect your Web3 wallet to access NFT marketplace and blockchain features.
              </p>

              <div className="space-y-3">
                {connectors.map((connector) => (
                  <motion.button
                    key={connector.id}
                    onClick={() => handleConnect(connector.id)}
                    disabled={!connector.ready || isConnecting}
                    className="w-full p-4 border border-gray-200 dark:border-gray-700 rounded-xl hover:border-purple-300 dark:hover:border-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                  >
                    <div className="flex items-center gap-3">
                      <span className="text-2xl">{connector.icon}</span>
                      <div className="text-left">
                        <div className="font-medium text-gray-900 dark:text-white">
                          {connector.name}
                        </div>
                        {!connector.ready && (
                          <div className="text-xs text-gray-500">Not installed</div>
                        )}
                      </div>
                      {isConnecting && (
                        <div className="ml-auto">
                          <div className="w-4 h-4 border-2 border-purple-500 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                      )}
                    </div>
                  </motion.button>
                ))}
              </div>
            </motion.div>
          ) : (
            <motion.div
              key="connected"
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -20 }}
              className="space-y-4"
            >
              {/* Connection Status */}
              <div className="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                <div className="flex items-center gap-3">
                  <CheckCircle className="w-5 h-5 text-green-600 dark:text-green-400" />
                  <div>
                    <div className="font-medium text-green-800 dark:text-green-200">
                      Wallet Connected
                    </div>
                    <div className="text-sm text-green-600 dark:text-green-400">
                      {formattedAddress}
                    </div>
                  </div>
                </div>
                <Button
                  onClick={disconnectWallet}
                  variant="ghost"
                  size="sm"
                  icon={<Power className="w-4 h-4" />}
                />
              </div>

              {/* Network Status */}
              {chain && (
                <div className={`p-4 rounded-xl ${
                  isNetworkSupported 
                    ? 'bg-blue-50 dark:bg-blue-900/20' 
                    : 'bg-orange-50 dark:bg-orange-900/20'
                }`}>
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      {isNetworkSupported ? (
                        <CheckCircle className="w-5 h-5 text-blue-600 dark:text-blue-400" />
                      ) : (
                        <AlertTriangle className="w-5 h-5 text-orange-600 dark:text-orange-400" />
                      )}
                      <div>
                        <div className={`font-medium ${
                          isNetworkSupported 
                            ? 'text-blue-800 dark:text-blue-200' 
                            : 'text-orange-800 dark:text-orange-200'
                        }`}>
                          {networkConfig?.name || chain.name}
                        </div>
                        <div className={`text-sm ${
                          isNetworkSupported 
                            ? 'text-blue-600 dark:text-blue-400' 
                            : 'text-orange-600 dark:text-orange-400'
                        }`}>
                          {isNetworkSupported ? 'Supported network' : 'Unsupported network'}
                        </div>
                      </div>
                    </div>
                    
                    <div className="flex items-center gap-2">
                      {networkConfig && (
                        <Button
                          onClick={() => window.open(`${networkConfig.explorerUrl}/address/${address}`, '_blank')}
                          variant="ghost"
                          size="sm"
                          icon={<ExternalLink className="w-4 h-4" />}
                        />
                      )}
                      
                      {needsNetworkSwitch && (
                        <Button
                          onClick={handleNetworkSwitch}
                          loading={isSwitchingNetwork}
                          variant="outline"
                          size="sm"
                        >
                          Switch Network
                        </Button>
                      )}
                    </div>
                  </div>
                </div>
              )}

              {/* Action Buttons */}
              {isNetworkSupported && (
                <div className="pt-4 border-t border-gray-200 dark:border-gray-700">
                  <div className="grid grid-cols-2 gap-3">
                    <Button variant="outline" size="sm">
                      View NFTs
                    </Button>
                    <Button variant="primary" size="sm">
                      Marketplace
                    </Button>
                  </div>
                </div>
              )}
            </motion.div>
          )}
        </AnimatePresence>
      </CardContent>
    </Card>
  );
};