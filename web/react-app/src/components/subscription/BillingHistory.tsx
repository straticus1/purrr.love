import React from 'react';
import { motion } from 'framer-motion';
import { Download, Calendar, DollarSign, Check, Clock, X } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { useSubscription } from '@/hooks/useSubscription';
import { Invoice } from '@/types/subscription';
import { formatDistanceToNow, format } from 'date-fns';

export const BillingHistory: React.FC = () => {
  const {
    invoices,
    isLoadingInvoices,
    invoicesError,
  } = useSubscription();

  const getStatusIcon = (status: Invoice['status']) => {
    switch (status) {
      case 'paid':
        return <Check className="w-4 h-4 text-green-600 dark:text-green-400" />;
      case 'pending':
        return <Clock className="w-4 h-4 text-yellow-600 dark:text-yellow-400" />;
      case 'unpaid':
        return <X className="w-4 h-4 text-red-600 dark:text-red-400" />;
      default:
        return <Clock className="w-4 h-4 text-gray-600 dark:text-gray-400" />;
    }
  };

  const getStatusColor = (status: Invoice['status']) => {
    switch (status) {
      case 'paid':
        return 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border-green-200 dark:border-green-800';
      case 'pending':
        return 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 border-yellow-200 dark:border-yellow-800';
      case 'unpaid':
        return 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 border-red-200 dark:border-red-800';
      default:
        return 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-200 dark:border-gray-700';
    }
  };

  const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency.toUpperCase(),
    }).format(amount / 100); // Stripe amounts are in cents
  };

  const handleDownloadInvoice = (invoice: Invoice) => {
    if (invoice.invoiceUrl) {
      window.open(invoice.invoiceUrl, '_blank');
    }
  };

  if (isLoadingInvoices) {
    return (
      <Card>
        <CardContent className="p-8 text-center">
          <div className="animate-spin w-8 h-8 border-2 border-purple-500 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p className="text-gray-600 dark:text-gray-400">Loading billing history...</p>
        </CardContent>
      </Card>
    );
  }

  if (invoicesError) {
    return (
      <Card>
        <CardContent className="p-8 text-center">
          <X className="w-12 h-12 text-red-500 mx-auto mb-4" />
          <p className="text-red-600 dark:text-red-400 mb-4">
            Failed to load billing history
          </p>
          <Button variant="outline" onClick={() => window.location.reload()}>
            Try Again
          </Button>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Calendar className="w-5 h-5" />
          Billing History
        </CardTitle>
      </CardHeader>
      
      <CardContent>
        {invoices.length === 0 ? (
          <div className="text-center py-8">
            <DollarSign className="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p className="text-gray-600 dark:text-gray-400 mb-2">
              No billing history yet
            </p>
            <p className="text-sm text-gray-500 dark:text-gray-500">
              Your invoices and payment history will appear here once you have an active subscription.
            </p>
          </div>
        ) : (
          <div className="space-y-4">
            {invoices.map((invoice, index) => (
              <motion.div
                key={invoice.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.3, delay: index * 0.1 }}
                className="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
              >
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center text-white">
                      <DollarSign className="w-6 h-6" />
                    </div>
                    
                    <div>
                      <div className="flex items-center gap-3 mb-1">
                        <h3 className="font-medium text-gray-900 dark:text-white">
                          {formatCurrency(invoice.amount, invoice.currency)}
                        </h3>
                        
                        <div className={`px-2 py-1 rounded-full text-xs font-medium border flex items-center gap-1 ${getStatusColor(invoice.status)}`}>
                          {getStatusIcon(invoice.status)}
                          <span className="capitalize">{invoice.status}</span>
                        </div>
                      </div>
                      
                      <div className="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                        <span>
                          Due: {format(new Date(invoice.dueDate), 'MMM dd, yyyy')}
                        </span>
                        
                        {invoice.paidAt && (
                          <span>
                            Paid: {format(new Date(invoice.paidAt), 'MMM dd, yyyy')}
                          </span>
                        )}
                        
                        <span>
                          {formatDistanceToNow(new Date(invoice.createdAt), { addSuffix: true })}
                        </span>
                      </div>
                    </div>
                  </div>

                  <div className="flex items-center gap-2">
                    {invoice.status === 'unpaid' && (
                      <Button
                        variant="outline"
                        size="sm"
                        className="text-orange-600 hover:text-orange-700 border-orange-200 hover:border-orange-300"
                      >
                        Pay Now
                      </Button>
                    )}
                    
                    {invoice.invoiceUrl && (
                      <Button
                        onClick={() => handleDownloadInvoice(invoice)}
                        variant="ghost"
                        size="sm"
                        icon={<Download className="w-4 h-4" />}
                      >
                        Download
                      </Button>
                    )}
                  </div>
                </div>
              </motion.div>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  );
};