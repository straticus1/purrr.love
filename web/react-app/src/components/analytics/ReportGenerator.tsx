import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Download, FileText, Table, Code, Calendar, Settings, Check } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { useAnalytics, DateRange } from '@/hooks/useAnalytics';

interface ReportGeneratorProps {
  dateRange: DateRange;
  selectedMetrics: string[];
  className?: string;
}

type ReportFormat = 'pdf' | 'csv' | 'json';
type ReportType = 'summary' | 'detailed' | 'executive';

const formatOptions = [
  {
    type: 'pdf' as ReportFormat,
    label: 'PDF Report',
    description: 'Formatted document with charts and insights',
    icon: FileText,
    color: 'text-red-600',
    bgColor: 'bg-red-100 dark:bg-red-900',
  },
  {
    type: 'csv' as ReportFormat,
    label: 'CSV Export',
    description: 'Raw data for spreadsheet analysis',
    icon: Table,
    color: 'text-green-600',
    bgColor: 'bg-green-100 dark:bg-green-900',
  },
  {
    type: 'json' as ReportFormat,
    label: 'JSON Data',
    description: 'Structured data for developers',
    icon: Code,
    color: 'text-blue-600',
    bgColor: 'bg-blue-100 dark:bg-blue-900',
  },
];

const reportTypes = [
  {
    type: 'summary' as ReportType,
    label: 'Summary Report',
    description: 'Key metrics and trends overview',
  },
  {
    type: 'detailed' as ReportType,
    label: 'Detailed Analysis',
    description: 'Comprehensive data analysis',
  },
  {
    type: 'executive' as ReportType,
    label: 'Executive Summary',
    description: 'High-level insights for stakeholders',
  },
];

const availableMetrics = [
  { id: 'users', label: 'User Analytics', description: 'User growth, demographics, retention' },
  { id: 'engagement', label: 'Engagement Metrics', description: 'Session time, page views, interactions' },
  { id: 'revenue', label: 'Revenue Analytics', description: 'Sales, subscriptions, conversion rates' },
  { id: 'cats', label: 'Cat Analytics', description: 'Cat breeds, health metrics, adoptions' },
  { id: 'games', label: 'Game Analytics', description: 'Game plays, scores, achievements' },
];

export const ReportGenerator: React.FC<ReportGeneratorProps> = ({
  dateRange,
  selectedMetrics,
  className = '',
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [selectedFormat, setSelectedFormat] = useState<ReportFormat>('pdf');
  const [selectedReportType, setSelectedReportType] = useState<ReportType>('summary');
  const [selectedMetricsList, setSelectedMetricsList] = useState<string[]>(selectedMetrics);
  const [includeCharts, setIncludeCharts] = useState(true);
  const [includeRawData, setIncludeRawData] = useState(false);
  const [customTitle, setCustomTitle] = useState('');

  const { exportData, isGeneratingReport } = useAnalytics(dateRange);

  const handleMetricToggle = (metricId: string) => {
    setSelectedMetricsList(prev =>
      prev.includes(metricId)
        ? prev.filter(id => id !== metricId)
        : [...prev, metricId]
    );
  };

  const handleGenerate = () => {
    const reportConfig = {
      format: selectedFormat,
      reportType: selectedReportType,
      metrics: selectedMetricsList,
      dateRange,
      options: {
        includeCharts,
        includeRawData,
        customTitle: customTitle || `Purrr.love Analytics Report - ${selectedReportType}`,
      },
    };

    exportData(selectedFormat, selectedMetricsList);
    setIsOpen(false);
  };

  const canGenerate = selectedMetricsList.length > 0;

  return (
    <>
      <Button
        onClick={() => setIsOpen(true)}
        variant="primary"
        icon={<Download className="w-4 h-4" />}
        className={className}
      >
        Generate Report
      </Button>

      <AnimatePresence>
        {isOpen && (
          <>
            {/* Backdrop */}
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
              onClick={() => setIsOpen(false)}
            />

            {/* Modal */}
            <motion.div
              initial={{ opacity: 0, scale: 0.95, y: 20 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.95, y: 20 }}
              className="fixed inset-0 z-50 flex items-center justify-center p-4"
              onClick={(e) => e.stopPropagation()}
            >
              <Card className="w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between mb-6">
                    <div>
                      <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
                        Generate Report
                      </h2>
                      <p className="text-gray-600 dark:text-gray-400 mt-1">
                        Create a custom analytics report for your data
                      </p>
                    </div>
                    <button
                      onClick={() => setIsOpen(false)}
                      className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    >
                      <span className="sr-only">Close</span>
                      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                      </svg>
                    </button>
                  </div>

                  <div className="space-y-6">
                    {/* Custom Title */}
                    <div>
                      <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Report Title (Optional)
                      </label>
                      <input
                        type="text"
                        value={customTitle}
                        onChange={(e) => setCustomTitle(e.target.value)}
                        placeholder="Custom report title..."
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      />
                    </div>

                    {/* Report Type */}
                    <div>
                      <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Report Type
                      </h3>
                      <div className="grid grid-cols-1 gap-3">
                        {reportTypes.map((type) => (
                          <button
                            key={type.type}
                            onClick={() => setSelectedReportType(type.type)}
                            className={`p-3 rounded-lg border-2 text-left transition-all ${
                              selectedReportType === type.type
                                ? 'border-purple-500 bg-purple-50 dark:bg-purple-900'
                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                            }`}
                          >
                            <div className="flex items-center justify-between">
                              <div>
                                <div className="font-medium text-gray-900 dark:text-white">
                                  {type.label}
                                </div>
                                <div className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                  {type.description}
                                </div>
                              </div>
                              {selectedReportType === type.type && (
                                <Check className="w-5 h-5 text-purple-600" />
                              )}
                            </div>
                          </button>
                        ))}
                      </div>
                    </div>

                    {/* Format Selection */}
                    <div>
                      <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Export Format
                      </h3>
                      <div className="grid grid-cols-3 gap-3">
                        {formatOptions.map((format) => {
                          const Icon = format.icon;
                          return (
                            <button
                              key={format.type}
                              onClick={() => setSelectedFormat(format.type)}
                              className={`p-4 rounded-lg border-2 text-center transition-all ${
                                selectedFormat === format.type
                                  ? 'border-purple-500 bg-purple-50 dark:bg-purple-900'
                                  : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                              }`}
                            >
                              <div className={`w-8 h-8 mx-auto mb-2 p-1.5 rounded ${format.bgColor}`}>
                                <Icon className={`w-full h-full ${format.color}`} />
                              </div>
                              <div className="font-medium text-gray-900 dark:text-white text-sm">
                                {format.label}
                              </div>
                              <div className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                {format.description}
                              </div>
                            </button>
                          );
                        })}
                      </div>
                    </div>

                    {/* Metrics Selection */}
                    <div>
                      <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Include Metrics
                      </h3>
                      <div className="space-y-2">
                        {availableMetrics.map((metric) => (
                          <label
                            key={metric.id}
                            className="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer"
                          >
                            <input
                              type="checkbox"
                              checked={selectedMetricsList.includes(metric.id)}
                              onChange={() => handleMetricToggle(metric.id)}
                              className="mt-0.5 h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                            />
                            <div className="flex-1">
                              <div className="font-medium text-gray-900 dark:text-white">
                                {metric.label}
                              </div>
                              <div className="text-sm text-gray-600 dark:text-gray-400">
                                {metric.description}
                              </div>
                            </div>
                          </label>
                        ))}
                      </div>
                    </div>

                    {/* Options */}
                    <div>
                      <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Additional Options
                      </h3>
                      <div className="space-y-3">
                        <label className="flex items-center gap-3 cursor-pointer">
                          <input
                            type="checkbox"
                            checked={includeCharts}
                            onChange={(e) => setIncludeCharts(e.target.checked)}
                            className="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                          />
                          <div>
                            <div className="font-medium text-gray-900 dark:text-white">
                              Include Charts
                            </div>
                            <div className="text-sm text-gray-600 dark:text-gray-400">
                              Add visual charts to the report
                            </div>
                          </div>
                        </label>

                        <label className="flex items-center gap-3 cursor-pointer">
                          <input
                            type="checkbox"
                            checked={includeRawData}
                            onChange={(e) => setIncludeRawData(e.target.checked)}
                            className="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                          />
                          <div>
                            <div className="font-medium text-gray-900 dark:text-white">
                              Include Raw Data
                            </div>
                            <div className="text-sm text-gray-600 dark:text-gray-400">
                              Include detailed data tables
                            </div>
                          </div>
                        </label>
                      </div>
                    </div>

                    {/* Date Range Display */}
                    <div className="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                      <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <Calendar className="w-4 h-4" />
                        <span>
                          Report Period: {dateRange.startDate.toLocaleDateString()} - {dateRange.endDate.toLocaleDateString()}
                        </span>
                      </div>
                    </div>

                    {/* Actions */}
                    <div className="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                      <Button
                        onClick={handleGenerate}
                        loading={isGeneratingReport}
                        disabled={!canGenerate}
                        variant="primary"
                        icon={<Download className="w-4 h-4" />}
                        className="flex-1"
                      >
                        Generate Report
                      </Button>
                      <Button
                        onClick={() => setIsOpen(false)}
                        variant="outline"
                      >
                        Cancel
                      </Button>
                    </div>

                    {!canGenerate && (
                      <p className="text-sm text-red-600 text-center">
                        Please select at least one metric to include in the report.
                      </p>
                    )}
                  </div>
                </CardContent>
              </Card>
            </motion.div>
          </>
        )}
      </AnimatePresence>
    </>
  );
};