import React from 'react';
import { WagmiConfig } from 'wagmi';
import { config } from '@/lib/web3';

interface Web3ProviderProps {
  children: React.ReactNode;
}

export const Web3Provider: React.FC<Web3ProviderProps> = ({ children }) => {
  return (
    <WagmiConfig config={config}>
      {children}
    </WagmiConfig>
  );
};