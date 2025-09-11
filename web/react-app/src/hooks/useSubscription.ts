import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiClient, handleApiError } from '@/lib/api';
import { Subscription, SubscriptionTier, PaymentMethod, Invoice } from '@/types/subscription';

export const useSubscription = () => {
  const queryClient = useQueryClient();

  // Current subscription query
  const subscriptionQuery = useQuery({
    queryKey: ['subscription'],
    queryFn: () => apiClient.get<Subscription>('/subscription'),
    staleTime: 1000 * 60 * 5, // 5 minutes
  });

  // Payment methods query
  const paymentMethodsQuery = useQuery({
    queryKey: ['payment-methods'],
    queryFn: () => apiClient.get<PaymentMethod[]>('/payment-methods'),
    staleTime: 1000 * 60 * 10, // 10 minutes
  });

  // Invoices query
  const invoicesQuery = useQuery({
    queryKey: ['invoices'],
    queryFn: () => apiClient.get<Invoice[]>('/subscription/invoices'),
    staleTime: 1000 * 60 * 15, // 15 minutes
  });

  // Create checkout session mutation
  const createCheckoutSessionMutation = useMutation({
    mutationFn: (data: { 
      priceId: string; 
      tier: SubscriptionTier;
      billing: 'monthly' | 'yearly';
      successUrl?: string;
      cancelUrl?: string;
    }) => apiClient.post('/subscription/checkout', data),
    onSuccess: (data) => {
      if (data.url) {
        window.location.href = data.url;
      }
    },
  });

  // Create subscription mutation
  const createSubscriptionMutation = useMutation({
    mutationFn: (data: {
      tier: SubscriptionTier;
      paymentMethodId: string;
      billing: 'monthly' | 'yearly';
    }) => apiClient.post('/subscription', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['subscription'] });
      queryClient.invalidateQueries({ queryKey: ['user'] });
    },
  });

  // Update subscription mutation
  const updateSubscriptionMutation = useMutation({
    mutationFn: (data: {
      tier?: SubscriptionTier;
      billing?: 'monthly' | 'yearly';
    }) => apiClient.put('/subscription', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['subscription'] });
    },
  });

  // Cancel subscription mutation
  const cancelSubscriptionMutation = useMutation({
    mutationFn: (data: { cancelAtPeriodEnd: boolean }) => 
      apiClient.post('/subscription/cancel', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['subscription'] });
    },
  });

  // Resume subscription mutation
  const resumeSubscriptionMutation = useMutation({
    mutationFn: () => apiClient.post('/subscription/resume'),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['subscription'] });
    },
  });

  // Add payment method mutation
  const addPaymentMethodMutation = useMutation({
    mutationFn: (data: { paymentMethodId: string; setAsDefault?: boolean }) =>
      apiClient.post('/payment-methods', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['payment-methods'] });
    },
  });

  // Remove payment method mutation
  const removePaymentMethodMutation = useMutation({
    mutationFn: (paymentMethodId: string) =>
      apiClient.delete(`/payment-methods/${paymentMethodId}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['payment-methods'] });
    },
  });

  // Set default payment method mutation
  const setDefaultPaymentMethodMutation = useMutation({
    mutationFn: (paymentMethodId: string) =>
      apiClient.put(`/payment-methods/${paymentMethodId}/default`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['payment-methods'] });
    },
  });

  // Create setup intent for payment method
  const createSetupIntentMutation = useMutation({
    mutationFn: () => apiClient.post('/payment-methods/setup-intent'),
  });

  return {
    // Data
    subscription: subscriptionQuery.data,
    paymentMethods: paymentMethodsQuery.data || [],
    invoices: invoicesQuery.data || [],

    // Loading states
    isLoadingSubscription: subscriptionQuery.isLoading,
    isLoadingPaymentMethods: paymentMethodsQuery.isLoading,
    isLoadingInvoices: invoicesQuery.isLoading,

    // Error states
    subscriptionError: subscriptionQuery.error,
    paymentMethodsError: paymentMethodsQuery.error,
    invoicesError: invoicesQuery.error,

    // Actions
    createCheckoutSession: createCheckoutSessionMutation.mutate,
    createSubscription: createSubscriptionMutation.mutate,
    updateSubscription: updateSubscriptionMutation.mutate,
    cancelSubscription: cancelSubscriptionMutation.mutate,
    resumeSubscription: resumeSubscriptionMutation.mutate,
    addPaymentMethod: addPaymentMethodMutation.mutate,
    removePaymentMethod: removePaymentMethodMutation.mutate,
    setDefaultPaymentMethod: setDefaultPaymentMethodMutation.mutate,
    createSetupIntent: createSetupIntentMutation.mutate,

    // Action loading states
    isCreatingCheckoutSession: createCheckoutSessionMutation.isPending,
    isCreatingSubscription: createSubscriptionMutation.isPending,
    isUpdatingSubscription: updateSubscriptionMutation.isPending,
    isCancellingSubscription: cancelSubscriptionMutation.isPending,
    isResumingSubscription: resumeSubscriptionMutation.isPending,
    isAddingPaymentMethod: addPaymentMethodMutation.isPending,
    isRemovingPaymentMethod: removePaymentMethodMutation.isPending,
    isSettingDefaultPaymentMethod: setDefaultPaymentMethodMutation.isPending,
    isCreatingSetupIntent: createSetupIntentMutation.isPending,

    // Action errors
    checkoutError: createCheckoutSessionMutation.error,
    subscriptionCreateError: createSubscriptionMutation.error,
    subscriptionUpdateError: updateSubscriptionMutation.error,
    cancelError: cancelSubscriptionMutation.error,
    resumeError: resumeSubscriptionMutation.error,
    paymentMethodError: addPaymentMethodMutation.error,
    removePaymentMethodError: removePaymentMethodMutation.error,
    setDefaultError: setDefaultPaymentMethodMutation.error,
    setupIntentError: createSetupIntentMutation.error,

    // Computed values
    hasActiveSubscription: subscriptionQuery.data?.status === 'active',
    isTrialing: subscriptionQuery.data?.status === 'trialing',
    isPastDue: subscriptionQuery.data?.status === 'past_due',
    willCancelAtPeriodEnd: subscriptionQuery.data?.cancelAtPeriodEnd || false,
  };
};