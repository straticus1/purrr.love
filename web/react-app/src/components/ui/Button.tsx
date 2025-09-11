import React from 'react';
import { motion } from 'framer-motion';
import { Loader2 } from 'lucide-react';
import { useThemedStyles } from '@/contexts/ThemeContext';

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'success' | 'warning' | 'error' | 'ghost' | 'outline';
  size?: 'sm' | 'md' | 'lg' | 'xl';
  loading?: boolean;
  icon?: React.ReactNode;
  iconPosition?: 'left' | 'right';
  fullWidth?: boolean;
  rounded?: boolean;
}

export const Button: React.FC<ButtonProps> = ({
  children,
  variant = 'primary',
  size = 'md',
  loading = false,
  icon,
  iconPosition = 'left',
  fullWidth = false,
  rounded = false,
  disabled,
  className = '',
  ...props
}) => {
  const { colors } = useThemedStyles();

  const baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm gap-1.5',
    md: 'px-4 py-2 text-sm gap-2',
    lg: 'px-6 py-3 text-base gap-2',
    xl: 'px-8 py-4 text-lg gap-3',
  };

  const variantClasses = {
    primary: `bg-primary-500 text-white hover:bg-primary-600 focus:ring-primary-500 shadow-sm hover:shadow-md`,
    secondary: `bg-secondary-500 text-white hover:bg-secondary-600 focus:ring-secondary-500 shadow-sm hover:shadow-md`,
    success: `bg-success-500 text-white hover:bg-success-600 focus:ring-success-500 shadow-sm hover:shadow-md`,
    warning: `bg-warning-500 text-white hover:bg-warning-600 focus:ring-warning-500 shadow-sm hover:shadow-md`,
    error: `bg-error-500 text-white hover:bg-error-600 focus:ring-error-500 shadow-sm hover:shadow-md`,
    ghost: `bg-transparent text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:ring-gray-500`,
    outline: `bg-transparent border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 focus:ring-gray-500`,
  };

  const roundedClasses = rounded ? 'rounded-full' : 'rounded-xl';
  const widthClasses = fullWidth ? 'w-full' : '';

  const combinedClasses = `
    ${baseClasses}
    ${sizeClasses[size]}
    ${variantClasses[variant]}
    ${roundedClasses}
    ${widthClasses}
    ${className}
  `.trim().replace(/\s+/g, ' ');

  const iconElement = loading ? (
    <Loader2 className="animate-spin" size={size === 'sm' ? 14 : size === 'md' ? 16 : size === 'lg' ? 18 : 20} />
  ) : icon;

  return (
    <motion.button
      className={combinedClasses}
      disabled={disabled || loading}
      whileHover={{ scale: 1.02 }}
      whileTap={{ scale: 0.98 }}
      transition={{ duration: 0.1 }}
      {...props}
    >
      {iconElement && iconPosition === 'left' && iconElement}
      {children && <span>{children}</span>}
      {iconElement && iconPosition === 'right' && iconElement}
    </motion.button>
  );
};

// Button variants as separate components for convenience
export const PrimaryButton: React.FC<Omit<ButtonProps, 'variant'>> = (props) => (
  <Button variant="primary" {...props} />
);

export const SecondaryButton: React.FC<Omit<ButtonProps, 'variant'>> = (props) => (
  <Button variant="secondary" {...props} />
);

export const GhostButton: React.FC<Omit<ButtonProps, 'variant'>> = (props) => (
  <Button variant="ghost" {...props} />
);

export const OutlineButton: React.FC<Omit<ButtonProps, 'variant'>> = (props) => (
  <Button variant="outline" {...props} />
);