import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { CardElement, useElements, useStripe } from '@stripe/react-stripe-js';
import { Check, X } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import { useSubscription } from '@/hooks/useSubscription';
import toast from 'react-hot-toast';

interface PaymentMethodFormProps {
  onSuccess?: () => void;
  onCancel?: () => void;
  setAsDefault?: boolean;
}

export const PaymentMethodForm: React.FC<PaymentMethodFormProps> = ({
  onSuccess,
  onCancel,
  setAsDefault = false,
}) => {
  const stripe = useStripe();
  const elements = useElements();
  const [isProcessing, setIsProcessing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  const {
    createSetupIntent,
    addPaymentMethod,
    isCreatingSetupIntent,
    isAddingPaymentMethod,
    setupIntentError,
    paymentMethodError,
  } = useSubscription();

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    
    if (!stripe || !elements) {
      setError('Stripe has not loaded yet. Please try again.');
      return;
    }

    const cardElement = elements.getElement(CardElement);
    if (!cardElement) {
      setError('Card element not found. Please refresh and try again.');
      return;
    }

    setIsProcessing(true);
    setError(null);

    try {
      // Create setup intent on the server
      const setupIntentResponse = await new Promise<any>((resolve, reject) => {
        createSetupIntent(undefined, {
          onSuccess: resolve,
          onError: reject,
        });
      });

      if (!setupIntentResponse?.clientSecret) {
        throw new Error('Failed to create payment setup. Please try again.');
      }

      // Confirm setup intent with card
      const { error: confirmError, setupIntent } = await stripe.confirmCardSetup(
        setupIntentResponse.clientSecret,
        {
          payment_method: {
            card: cardElement,
          },
        }
      );

      if (confirmError) {
        throw new Error(confirmError.message || 'Payment setup failed');
      }

      if (!setupIntent?.payment_method) {
        throw new Error('Payment method not created');
      }

      // Add payment method to user account
      await new Promise<void>((resolve, reject) => {
        addPaymentMethod(
          { 
            paymentMethodId: setupIntent.payment_method as string,
            setAsDefault,
          },
          {
            onSuccess: resolve,
            onError: reject,
          }
        );
      });

      toast.success('Payment method added successfully!');
      onSuccess?.();

    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'An unexpected error occurred';
      setError(errorMessage);
      toast.error(errorMessage);
    } finally {
      setIsProcessing(false);
    }
  };

  const cardElementOptions = {
    style: {
      base: {
        fontSize: '16px',
        color: '#1f2937',
        fontFamily: 'Inter, system-ui, sans-serif',
        '::placeholder': {
          color: '#9ca3af',
        },
        iconColor: '#8b5cf6',
      },
      invalid: {
        color: '#ef4444',
        iconColor: '#ef4444',
      },
    },
    hidePostalCode: false,
  };

  const isLoading = isProcessing || isCreatingSetupIntent || isAddingPaymentMethod;

  return (
    <motion.form
      onSubmit={handleSubmit}
      className="p-6 bg-gray-50 dark:bg-gray-800 rounded-xl"
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.3 }}
    >
      <div className="mb-6">
        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
          Add Payment Method
        </h3>
        <p className="text-sm text-gray-600 dark:text-gray-400">
          Add a credit or debit card to your account for secure payments.
        </p>
      </div>

      <div className="mb-6">
        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Card Information
        </label>
        
        <div className="p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700">
          <CardElement
            options={cardElementOptions}
            onChange={(event) => {
              if (event.error) {
                setError(event.error.message || 'Card validation error');
              } else {
                setError(null);
              }
            }}
          />
        </div>
      </div>

      {error && (
        <motion.div
          initial={{ opacity: 0, y: -10 }}
          animate={{ opacity: 1, y: 0 }}
          className="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl"
        >
          <div className="flex items-center gap-2 text-red-600 dark:text-red-400">
            <X className="w-4 h-4" />
            <span className="text-sm">{error}</span>
          </div>
        </motion.div>
      )}

      <div className="flex items-center gap-3">
        <Button
          type="submit"
          disabled={!stripe || isLoading}
          loading={isLoading}
          icon={!isLoading ? <Check className="w-4 h-4" /> : undefined}
        >
          Add Payment Method
        </Button>
        
        <Button
          type="button"
          variant="ghost"
          onClick={onCancel}
          disabled={isLoading}
        >
          Cancel
        </Button>
      </div>

      <div className="mt-4 text-xs text-gray-500 dark:text-gray-400">
        <div className="flex items-center gap-1 mb-1">
          <div className="w-3 h-3 bg-green-500 rounded-full flex items-center justify-center">
            <div className="w-1 h-1 bg-white rounded-full"></div>
          </div>
          Your payment information is encrypted and secure
        </div>
        <div className="flex items-center gap-1">
          <div className="w-3 h-3 bg-blue-500 rounded-full flex items-center justify-center">
            <div className="w-1 h-1 bg-white rounded-full"></div>
          </div>
          Powered by Stripe - Industry-leading security
        </div>
      </div>
    </motion.form>
  );
};