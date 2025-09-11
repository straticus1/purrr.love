import React, { useState, useRef, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Calendar, ChevronLeft, ChevronRight, Clock } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';

interface DateRange {
  startDate: Date;
  endDate: Date;
}

interface DateRangePickerProps {
  dateRange: DateRange;
  onChange: (dateRange: DateRange) => void;
  maxDate?: Date;
  minDate?: Date;
  className?: string;
}

const PRESET_RANGES = [
  {
    label: 'Last 7 days',
    value: 'last7days',
    getDates: () => ({
      startDate: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000),
      endDate: new Date(),
    }),
  },
  {
    label: 'Last 30 days',
    value: 'last30days',
    getDates: () => ({
      startDate: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000),
      endDate: new Date(),
    }),
  },
  {
    label: 'Last 90 days',
    value: 'last90days',
    getDates: () => ({
      startDate: new Date(Date.now() - 90 * 24 * 60 * 60 * 1000),
      endDate: new Date(),
    }),
  },
  {
    label: 'This month',
    value: 'thisMonth',
    getDates: () => {
      const now = new Date();
      return {
        startDate: new Date(now.getFullYear(), now.getMonth(), 1),
        endDate: new Date(),
      };
    },
  },
  {
    label: 'Last month',
    value: 'lastMonth',
    getDates: () => {
      const now = new Date();
      const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
      return {
        startDate: lastMonth,
        endDate: new Date(now.getFullYear(), now.getMonth(), 0),
      };
    },
  },
  {
    label: 'This year',
    value: 'thisYear',
    getDates: () => {
      const now = new Date();
      return {
        startDate: new Date(now.getFullYear(), 0, 1),
        endDate: new Date(),
      };
    },
  },
];

const MONTHS = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
];

const DAYS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

export const DateRangePicker: React.FC<DateRangePickerProps> = ({
  dateRange,
  onChange,
  maxDate = new Date(),
  minDate = new Date(2020, 0, 1),
  className = '',
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [currentMonth, setCurrentMonth] = useState(new Date().getMonth());
  const [currentYear, setCurrentYear] = useState(new Date().getFullYear());
  const [selectionStart, setSelectionStart] = useState<Date | null>(null);
  const [hoveredDate, setHoveredDate] = useState<Date | null>(null);
  
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const formatDate = (date: Date): string => {
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  };

  const getDaysInMonth = (month: number, year: number): number => {
    return new Date(year, month + 1, 0).getDate();
  };

  const getFirstDayOfMonth = (month: number, year: number): number => {
    return new Date(year, month, 1).getDay();
  };

  const isDateInRange = (date: Date): boolean => {
    const start = selectionStart || dateRange.startDate;
    const end = hoveredDate || dateRange.endDate;
    
    if (start && end) {
      const actualStart = start <= end ? start : end;
      const actualEnd = start <= end ? end : start;
      return date >= actualStart && date <= actualEnd;
    }
    
    return false;
  };

  const isDateSelected = (date: Date): boolean => {
    const startDate = dateRange.startDate;
    const endDate = dateRange.endDate;
    
    return (startDate && date.toDateString() === startDate.toDateString()) ||
           (endDate && date.toDateString() === endDate.toDateString());
  };

  const isDateDisabled = (date: Date): boolean => {
    return date < minDate || date > maxDate;
  };

  const handleDateClick = (date: Date) => {
    if (isDateDisabled(date)) return;

    if (!selectionStart) {
      setSelectionStart(date);
    } else {
      const start = selectionStart <= date ? selectionStart : date;
      const end = selectionStart <= date ? date : selectionStart;
      
      onChange({ startDate: start, endDate: end });
      setSelectionStart(null);
      setIsOpen(false);
    }
  };

  const handlePresetClick = (preset: typeof PRESET_RANGES[0]) => {
    const dates = preset.getDates();
    onChange(dates);
    setSelectionStart(null);
    setIsOpen(false);
  };

  const navigateMonth = (direction: 'prev' | 'next') => {
    if (direction === 'prev') {
      if (currentMonth === 0) {
        setCurrentMonth(11);
        setCurrentYear(currentYear - 1);
      } else {
        setCurrentMonth(currentMonth - 1);
      }
    } else {
      if (currentMonth === 11) {
        setCurrentMonth(0);
        setCurrentYear(currentYear + 1);
      } else {
        setCurrentMonth(currentMonth + 1);
      }
    }
  };

  const renderCalendar = () => {
    const daysInMonth = getDaysInMonth(currentMonth, currentYear);
    const firstDay = getFirstDayOfMonth(currentMonth, currentYear);
    const days: (Date | null)[] = [];

    // Add empty cells for days before the first day of the month
    for (let i = 0; i < firstDay; i++) {
      days.push(null);
    }

    // Add all days in the month
    for (let day = 1; day <= daysInMonth; day++) {
      days.push(new Date(currentYear, currentMonth, day));
    }

    return (
      <div className="grid grid-cols-7 gap-1">
        {/* Day headers */}
        {DAYS.map(day => (
          <div key={day} className="h-8 flex items-center justify-center text-xs font-medium text-gray-500">
            {day}
          </div>
        ))}
        
        {/* Calendar days */}
        {days.map((date, index) => {
          if (!date) {
            return <div key={index} className="h-8" />;
          }

          const isSelected = isDateSelected(date);
          const isInRange = isDateInRange(date);
          const isDisabled = isDateDisabled(date);
          const isToday = date.toDateString() === new Date().toDateString();

          return (
            <button
              key={index}
              onClick={() => handleDateClick(date)}
              onMouseEnter={() => setHoveredDate(date)}
              onMouseLeave={() => setHoveredDate(null)}
              disabled={isDisabled}
              className={`
                h-8 flex items-center justify-center text-sm rounded transition-colors
                ${isDisabled 
                  ? 'text-gray-300 cursor-not-allowed' 
                  : 'hover:bg-purple-100 dark:hover:bg-purple-900 cursor-pointer'
                }
                ${isSelected 
                  ? 'bg-purple-600 text-white' 
                  : isInRange 
                    ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300' 
                    : 'text-gray-700 dark:text-gray-300'
                }
                ${isToday && !isSelected 
                  ? 'ring-2 ring-purple-600 ring-inset' 
                  : ''
                }
              `}
            >
              {date.getDate()}
            </button>
          );
        })}
      </div>
    );
  };

  return (
    <div ref={containerRef} className={`relative ${className}`}>
      <Button
        onClick={() => setIsOpen(!isOpen)}
        variant="outline"
        icon={<Calendar className="w-4 h-4" />}
        className="min-w-64"
      >
        {formatDate(dateRange.startDate)} - {formatDate(dateRange.endDate)}
      </Button>

      <AnimatePresence>
        {isOpen && (
          <motion.div
            initial={{ opacity: 0, y: 8, scale: 0.95 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 8, scale: 0.95 }}
            transition={{ duration: 0.2 }}
            className="absolute top-full left-0 mt-2 z-50"
          >
            <Card className="w-80 shadow-lg">
              <CardContent className="p-4">
                {/* Presets */}
                <div className="mb-4">
                  <h4 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Quick Select
                  </h4>
                  <div className="grid grid-cols-2 gap-2">
                    {PRESET_RANGES.map((preset) => (
                      <button
                        key={preset.value}
                        onClick={() => handlePresetClick(preset)}
                        className="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-left"
                      >
                        {preset.label}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="border-t border-gray-200 dark:border-gray-700 pt-4">
                  {/* Month/Year Navigation */}
                  <div className="flex items-center justify-between mb-4">
                    <button
                      onClick={() => navigateMonth('prev')}
                      className="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    >
                      <ChevronLeft className="w-4 h-4" />
                    </button>
                    
                    <div className="text-sm font-medium text-gray-900 dark:text-white">
                      {MONTHS[currentMonth]} {currentYear}
                    </div>
                    
                    <button
                      onClick={() => navigateMonth('next')}
                      className="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    >
                      <ChevronRight className="w-4 h-4" />
                    </button>
                  </div>

                  {/* Calendar */}
                  {renderCalendar()}

                  {/* Instructions */}
                  <div className="mt-4 text-xs text-gray-500 text-center">
                    {selectionStart 
                      ? 'Click end date to complete selection' 
                      : 'Click start date to begin selection'
                    }
                  </div>
                </div>
              </CardContent>
            </Card>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};