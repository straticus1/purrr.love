import React, { forwardRef } from 'react';
import { motion } from 'framer-motion';
import { AlertCircle, Eye, EyeOff } from 'lucide-react';
import { useThemedStyles } from '@/contexts/ThemeContext';

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  helperText?: string;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  variant?: 'default' | 'filled' | 'outlined';
  inputSize?: 'sm' | 'md' | 'lg';
  rounded?: boolean;
}

export const Input = forwardRef<HTMLInputElement, InputProps>(({
  label,
  error,
  helperText,
  leftIcon,
  rightIcon,
  variant = 'default',
  inputSize = 'md',
  rounded = false,
  className = '',
  type = 'text',
  ...props
}, ref) => {
  const { colors } = useThemedStyles();
  const [showPassword, setShowPassword] = React.useState(false);
  const [isFocused, setIsFocused] = React.useState(false);

  const isPassword = type === 'password';
  const actualType = isPassword && showPassword ? 'text' : type;

  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-4 py-3 text-base',
  };

  const variantClasses = {
    default: `border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:border-primary-500 focus:ring-primary-500`,
    filled: `bg-gray-100 dark:bg-gray-700 border border-transparent focus:bg-white dark:focus:bg-gray-800 focus:border-primary-500 focus:ring-primary-500`,
    outlined: `border-2 border-gray-300 dark:border-gray-600 bg-transparent focus:border-primary-500 focus:ring-primary-500`,
  };

  const baseInputClasses = `
    w-full
    transition-all duration-200
    focus:outline-none focus:ring-2 focus:ring-opacity-50
    disabled:opacity-50 disabled:cursor-not-allowed
    text-gray-900 dark:text-white
    placeholder-gray-500 dark:placeholder-gray-400
  `;

  const roundedClass = rounded ? 'rounded-full' : 'rounded-xl';

  const inputClasses = `
    ${baseInputClasses}
    ${sizeClasses[inputSize]}
    ${variantClasses[variant]}
    ${roundedClass}
    ${leftIcon ? 'pl-10' : ''}
    ${rightIcon || isPassword ? 'pr-10' : ''}
    ${error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''}
    ${className}
  `.trim().replace(/\s+/g, ' ');

  const iconSizeClass = inputSize === 'sm' ? 'w-4 h-4' : inputSize === 'lg' ? 'w-6 h-6' : 'w-5 h-5';

  return (
    <div className="w-full">
      {label && (
        <motion.label
          className={`block text-sm font-medium mb-2 transition-colors duration-200 ${
            error 
              ? 'text-red-600 dark:text-red-400' 
              : isFocused 
                ? 'text-primary-600 dark:text-primary-400' 
                : 'text-gray-700 dark:text-gray-300'
          }`}
          animate={{ color: error ? '#dc2626' : isFocused ? '#2563eb' : undefined }}
        >
          {label}
        </motion.label>
      )}

      <div className="relative">
        {leftIcon && (
          <div className={`absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none ${iconSizeClass}`}>
            {leftIcon}
          </div>
        )}

        <motion.input
          ref={ref}
          type={actualType}
          className={inputClasses}
          onFocus={(e) => {
            setIsFocused(true);
            props.onFocus?.(e);
          }}
          onBlur={(e) => {
            setIsFocused(false);
            props.onBlur?.(e);
          }}
          whileFocus={{ scale: 1.02 }}
          transition={{ duration: 0.1 }}
          {...props}
        />

        {(rightIcon || isPassword) && (
          <div className="absolute right-3 top-1/2 transform -translate-y-1/2">
            {isPassword ? (
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className={`text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors ${iconSizeClass}`}
                tabIndex={-1}
              >
                {showPassword ? <EyeOff /> : <Eye />}
              </button>
            ) : (
              <div className={`text-gray-400 pointer-events-none ${iconSizeClass}`}>
                {rightIcon}
              </div>
            )}
          </div>
        )}

        {error && (
          <div className="absolute right-3 top-1/2 transform -translate-y-1/2">
            <AlertCircle className={`text-red-500 ${iconSizeClass} ${isPassword || rightIcon ? 'mr-8' : ''}`} />
          </div>
        )}
      </div>

      {(error || helperText) && (
        <motion.div
          className="mt-1"
          initial={{ opacity: 0, y: -5 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.2 }}
        >
          {error ? (
            <p className="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
              <AlertCircle className="w-4 h-4" />
              {error}
            </p>
          ) : (
            <p className="text-sm text-gray-500 dark:text-gray-400">
              {helperText}
            </p>
          )}
        </motion.div>
      )}
    </div>
  );
});

Input.displayName = 'Input';

// Specialized input components
export const SearchInput: React.FC<Omit<InputProps, 'type' | 'leftIcon'>> = (props) => (
  <Input 
    type="search" 
    leftIcon={<svg className="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>}
    {...props} 
  />
);

export const PasswordInput: React.FC<Omit<InputProps, 'type'>> = (props) => (
  <Input type="password" {...props} />
);

export const EmailInput: React.FC<Omit<InputProps, 'type'>> = (props) => (
  <Input type="email" {...props} />
);