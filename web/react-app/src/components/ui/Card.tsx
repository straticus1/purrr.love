import React from 'react';
import { motion } from 'framer-motion';
import { useThemedStyles } from '@/contexts/ThemeContext';

interface CardProps {
  children: React.ReactNode;
  className?: string;
  variant?: 'default' | 'elevated' | 'bordered' | 'glass';
  hover?: boolean;
  clickable?: boolean;
  onClick?: () => void;
  padding?: 'none' | 'sm' | 'md' | 'lg' | 'xl';
  rounded?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl';
}

export const Card: React.FC<CardProps> = ({
  children,
  className = '',
  variant = 'default',
  hover = false,
  clickable = false,
  onClick,
  padding = 'md',
  rounded = 'xl',
}) => {
  const { colors, isDark } = useThemedStyles();

  const baseClasses = 'transition-all duration-200';

  const variantClasses = {
    default: `bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700`,
    elevated: `bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl`,
    bordered: `bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-600`,
    glass: `bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-white/20 dark:border-gray-700/20`,
  };

  const paddingClasses = {
    none: '',
    sm: 'p-3',
    md: 'p-4',
    lg: 'p-6',
    xl: 'p-8',
  };

  const roundedClasses = {
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    xl: 'rounded-xl',
    '2xl': 'rounded-2xl',
    '3xl': 'rounded-3xl',
  };

  const hoverClasses = hover ? 'hover:shadow-lg hover:-translate-y-1' : '';
  const clickableClasses = clickable ? 'cursor-pointer hover:shadow-md' : '';

  const combinedClasses = `
    ${baseClasses}
    ${variantClasses[variant]}
    ${paddingClasses[padding]}
    ${roundedClasses[rounded]}
    ${hoverClasses}
    ${clickableClasses}
    ${className}
  `.trim().replace(/\s+/g, ' ');

  const MotionComponent = hover || clickable ? motion.div : 'div';

  const motionProps = hover || clickable ? {
    whileHover: { scale: 1.02, y: -2 },
    whileTap: clickable ? { scale: 0.98 } : undefined,
    transition: { duration: 0.2 },
  } : {};

  return (
    <MotionComponent
      className={combinedClasses}
      onClick={onClick}
      {...motionProps}
    >
      {children}
    </MotionComponent>
  );
};

// Card sub-components for better composition
export const CardHeader: React.FC<{
  children: React.ReactNode;
  className?: string;
}> = ({ children, className = '' }) => (
  <div className={`border-b border-gray-200 dark:border-gray-700 pb-4 mb-4 ${className}`}>
    {children}
  </div>
);

export const CardTitle: React.FC<{
  children: React.ReactNode;
  className?: string;
}> = ({ children, className = '' }) => (
  <h3 className={`text-lg font-semibold text-gray-900 dark:text-white ${className}`}>
    {children}
  </h3>
);

export const CardDescription: React.FC<{
  children: React.ReactNode;
  className?: string;
}> = ({ children, className = '' }) => (
  <p className={`text-sm text-gray-600 dark:text-gray-400 ${className}`}>
    {children}
  </p>
);

export const CardContent: React.FC<{
  children: React.ReactNode;
  className?: string;
}> = ({ children, className = '' }) => (
  <div className={className}>
    {children}
  </div>
);

export const CardFooter: React.FC<{
  children: React.ReactNode;
  className?: string;
  justify?: 'start' | 'center' | 'end' | 'between';
}> = ({ children, className = '', justify = 'end' }) => {
  const justifyClasses = {
    start: 'justify-start',
    center: 'justify-center',
    end: 'justify-end',
    between: 'justify-between',
  };

  return (
    <div className={`flex ${justifyClasses[justify]} gap-3 pt-4 mt-4 border-t border-gray-200 dark:border-gray-700 ${className}`}>
      {children}
    </div>
  );
};