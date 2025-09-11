export type SubscriptionTier = 'free' | 'premium' | 'pro';

export interface SubscriptionPlan {
  id: string;
  tier: SubscriptionTier;
  name: string;
  description: string;
  monthlyPrice: number;
  yearlyPrice: number;
  stripePriceIds: {
    monthly: string;
    yearly: string;
  };
  features: {
    name: string;
    included: boolean;
    limit?: number | 'unlimited';
  }[];
  popular?: boolean;
}

export interface Subscription {
  id: string;
  userId: string;
  tier: SubscriptionTier;
  status: 'active' | 'inactive' | 'cancelled' | 'past_due' | 'trialing';
  currentPeriodStart: string;
  currentPeriodEnd: string;
  cancelAtPeriodEnd: boolean;
  stripeSubscriptionId?: string;
  stripeCustomerId?: string;
  createdAt: string;
  updatedAt: string;
}

export interface PaymentMethod {
  id: string;
  type: 'card';
  card: {
    brand: string;
    last4: string;
    expMonth: number;
    expYear: number;
  };
  isDefault: boolean;
}

export interface Invoice {
  id: string;
  subscriptionId: string;
  amount: number;
  currency: string;
  status: 'paid' | 'unpaid' | 'pending';
  dueDate: string;
  paidAt?: string;
  invoiceUrl?: string;
  createdAt: string;
}

// Subscription plans configuration
export const SUBSCRIPTION_PLANS: SubscriptionPlan[] = [
  {
    id: 'free',
    tier: 'free',
    name: 'Free',
    description: 'Perfect for getting started with your first cat',
    monthlyPrice: 0,
    yearlyPrice: 0,
    stripePriceIds: {
      monthly: '',
      yearly: '',
    },
    features: [
      { name: 'Up to 3 cats', included: true, limit: 3 },
      { name: 'Basic cat care features', included: true },
      { name: 'Limited marketplace access', included: true },
      { name: 'Basic health monitoring', included: true },
      { name: 'Community games', included: true },
      { name: 'AI personality analysis', included: false },
      { name: 'Advanced breeding features', included: false },
      { name: 'VR experiences', included: false },
      { name: 'NFT marketplace', included: false },
      { name: 'Priority support', included: false },
    ],
  },
  {
    id: 'premium',
    tier: 'premium',
    name: 'Premium',
    description: 'Enhanced features for serious cat enthusiasts',
    monthlyPrice: 9.99,
    yearlyPrice: 99.99,
    popular: true,
    stripePriceIds: {
      monthly: 'price_premium_monthly', // Replace with actual Stripe price IDs
      yearly: 'price_premium_yearly',
    },
    features: [
      { name: 'Up to 15 cats', included: true, limit: 15 },
      { name: 'All basic features', included: true },
      { name: 'Full marketplace access', included: true },
      { name: 'Advanced health analytics', included: true },
      { name: 'Exclusive premium games', included: true },
      { name: 'AI personality analysis', included: true },
      { name: 'Basic breeding features', included: true },
      { name: 'Priority customer support', included: true },
      { name: 'VR experiences', included: false },
      { name: 'NFT marketplace', included: false },
    ],
  },
  {
    id: 'pro',
    tier: 'pro',
    name: 'Pro',
    description: 'Ultimate experience with unlimited features',
    monthlyPrice: 24.99,
    yearlyPrice: 249.99,
    stripePriceIds: {
      monthly: 'price_pro_monthly', // Replace with actual Stripe price IDs
      yearly: 'price_pro_yearly',
    },
    features: [
      { name: 'Unlimited cats', included: true, limit: 'unlimited' },
      { name: 'All premium features', included: true },
      { name: 'VR cat experiences', included: true },
      { name: 'NFT marketplace access', included: true },
      { name: 'Advanced breeding & genetics', included: true },
      { name: 'Custom cat personalities', included: true },
      { name: 'Exclusive pro tournaments', included: true },
      { name: '24/7 priority support', included: true },
      { name: 'Early access to new features', included: true },
      { name: 'White-label options', included: true },
    ],
  },
];