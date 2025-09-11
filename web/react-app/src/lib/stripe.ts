import { loadStripe, Stripe } from '@stripe/stripe-js';

// Get publishable key from environment
const stripePublishableKey = import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY;

if (!stripePublishableKey) {
  console.warn('Stripe publishable key not found in environment variables');
}

// Initialize Stripe
let stripePromise: Promise<Stripe | null>;

const getStripe = () => {
  if (!stripePromise) {
    stripePromise = loadStripe(stripePublishableKey || '');
  }
  return stripePromise;
};

export default getStripe;

// Stripe configuration
export const stripeConfig = {
  publishableKey: stripePublishableKey,
  appearance: {
    theme: 'stripe' as const,
    variables: {
      colorPrimary: '#8b5cf6', // Purple primary
      colorBackground: '#ffffff',
      colorText: '#1f2937',
      colorDanger: '#ef4444',
      fontFamily: 'Inter, system-ui, sans-serif',
      spacingUnit: '4px',
      borderRadius: '12px',
    },
    rules: {
      '.Tab': {
        padding: '12px 16px',
        border: '1px solid #e5e7eb',
        borderRadius: '8px',
        marginBottom: '8px',
      },
      '.Tab:hover': {
        backgroundColor: '#f9fafb',
      },
      '.Tab--selected': {
        backgroundColor: '#8b5cf6',
        color: '#ffffff',
      },
      '.Input': {
        padding: '12px 16px',
        border: '1px solid #d1d5db',
        borderRadius: '8px',
        fontSize: '14px',
      },
      '.Input:focus': {
        borderColor: '#8b5cf6',
        boxShadow: '0 0 0 2px rgba(139, 92, 246, 0.1)',
      },
      '.Label': {
        fontSize: '14px',
        fontWeight: '500',
        marginBottom: '4px',
        color: '#374151',
      },
    },
  },
};