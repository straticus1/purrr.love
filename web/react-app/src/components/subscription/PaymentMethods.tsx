import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { CreditCard, Plus, Trash2, Check, Star } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { useSubscription } from '@/hooks/useSubscription';
import { PaymentMethod } from '@/types/subscription';
import { PaymentMethodForm } from './PaymentMethodForm';

export const PaymentMethods: React.FC = () => {
  const [showAddForm, setShowAddForm] = useState(false);
  const {
    paymentMethods,
    isLoadingPaymentMethods,
    removePaymentMethod,
    setDefaultPaymentMethod,
    isRemovingPaymentMethod,
    isSettingDefaultPaymentMethod,
  } = useSubscription();

  const getCardBrandIcon = (brand: string) => {
    const brandIcons: Record<string, string> = {
      visa: '💳',
      mastercard: '💳',
      amex: '💳',
      discover: '💳',
      jcb: '💳',
      diners: '💳',
      unionpay: '💳',
    };
    return brandIcons[brand.toLowerCase()] || '💳';
  };

  const formatExpiryDate = (month: number, year: number) => {
    return `${month.toString().padStart(2, '0')}/${year.toString().slice(-2)}`;
  };

  const handleRemovePaymentMethod = (paymentMethodId: string) => {
    if (confirm('Are you sure you want to remove this payment method?')) {
      removePaymentMethod(paymentMethodId);
    }
  };

  const handleSetDefault = (paymentMethodId: string) => {
    setDefaultPaymentMethod(paymentMethodId);
  };

  if (isLoadingPaymentMethods) {
    return (
      <Card>
        <CardContent className="p-8 text-center">
          <div className="animate-spin w-8 h-8 border-2 border-purple-500 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p className="text-gray-600 dark:text-gray-400">Loading payment methods...</p>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center justify-between">
          <span>Payment Methods</span>
          <Button
            onClick={() => setShowAddForm(true)}
            size="sm"
            icon={<Plus className="w-4 h-4" />}
          >
            Add Card
          </Button>
        </CardTitle>
      </CardHeader>
      
      <CardContent className="space-y-4">
        {paymentMethods.length === 0 ? (
          <div className="text-center py-8">
            <CreditCard className="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p className="text-gray-600 dark:text-gray-400 mb-4">
              No payment methods added yet
            </p>
            <Button
              onClick={() => setShowAddForm(true)}
              variant="outline"
              icon={<Plus className="w-4 h-4" />}
            >
              Add Your First Card
            </Button>
          </div>
        ) : (
          <div className="space-y-4">
            {paymentMethods.map((paymentMethod, index) => (
              <motion.div
                key={paymentMethod.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.3, delay: index * 0.1 }}
                className={`p-4 rounded-xl border transition-colors ${
                  paymentMethod.isDefault
                    ? 'border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20'
                    : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'
                }`}
              >
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center text-white text-xl">
                      {getCardBrandIcon(paymentMethod.card.brand)}
                    </div>
                    
                    <div>
                      <div className="flex items-center gap-2">
                        <p className="font-medium text-gray-900 dark:text-white">
                          •••• •••• •••• {paymentMethod.card.last4}
                        </p>
                        {paymentMethod.isDefault && (
                          <div className="flex items-center gap-1 px-2 py-1 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded-full text-xs font-medium">
                            <Star className="w-3 h-3" />
                            Default
                          </div>
                        )}
                      </div>
                      
                      <div className="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                        <span className="capitalize">{paymentMethod.card.brand}</span>
                        <span>Expires {formatExpiryDate(paymentMethod.card.expMonth, paymentMethod.card.expYear)}</span>
                      </div>
                    </div>
                  </div>

                  <div className="flex items-center gap-2">
                    {!paymentMethod.isDefault && (
                      <Button
                        onClick={() => handleSetDefault(paymentMethod.id)}
                        variant="ghost"
                        size="sm"
                        disabled={isSettingDefaultPaymentMethod}
                      >
                        Set Default
                      </Button>
                    )}
                    
                    <Button
                      onClick={() => handleRemovePaymentMethod(paymentMethod.id)}
                      variant="ghost"
                      size="sm"
                      disabled={isRemovingPaymentMethod || paymentMethods.length === 1}
                      className="text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20"
                      icon={<Trash2 className="w-4 h-4" />}
                    />
                  </div>
                </div>
              </motion.div>
            ))}
          </div>
        )}

        <AnimatePresence>
          {showAddForm && (
            <motion.div
              initial={{ opacity: 0, height: 0 }}
              animate={{ opacity: 1, height: 'auto' }}
              exit={{ opacity: 0, height: 0 }}
              transition={{ duration: 0.3 }}
            >
              <PaymentMethodForm
                onSuccess={() => setShowAddForm(false)}
                onCancel={() => setShowAddForm(false)}
              />
            </motion.div>
          )}
        </AnimatePresence>
      </CardContent>
    </Card>
  );
};