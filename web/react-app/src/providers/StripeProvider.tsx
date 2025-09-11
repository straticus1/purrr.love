import React from 'react';
import { Elements } from '@stripe/react-stripe-js';
import getStripe, { stripeConfig } from '@/lib/stripe';

interface StripeProviderProps {
  children: React.ReactNode;
  clientSecret?: string;
}

export const StripeProvider: React.FC<StripeProviderProps> = ({ 
  children, 
  clientSecret 
}) => {
  const stripePromise = getStripe();

  const options = {
    clientSecret,
    appearance: stripeConfig.appearance,
  };

  return (
    <Elements stripe={stripePromise} options={clientSecret ? options : undefined}>
      {children}
    </Elements>
  );
};