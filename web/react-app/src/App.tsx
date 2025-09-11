import React from 'react';
import { BrowserRouter as Router, Routes, Route, Link, useLocation } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import ErrorBoundary from '@/components/ErrorBoundary';
import { QueryProvider } from '@/providers/QueryProvider';
import { ThemeProvider } from '@/contexts/ThemeContext';
import { Web3Provider } from '@/providers/Web3Provider';
import { 
  Home, 
  Users, 
  Heart, 
  Gamepad2, 
  Store, 
  Brain, 
  Settings, 
  LogOut,
  Menu,
  X,
  Bell,
  Search,
  User
} from 'lucide-react';
import CatManagement from '@/pages/CatManagement';
import { NFTMarketplace } from '@/pages/NFTMarketplace';
import { Subscription } from '@/pages/Subscription';
import { VirtualStore } from '@/pages/VirtualStore';

// Navigation items
const navigationItems = [
  { name: 'Dashboard', icon: Home, path: '/', color: 'from-blue-500 to-blue-600' },
  { name: 'Cats', icon: Users, path: '/cats', color: 'from-purple-500 to-purple-600' },
  { name: 'Store', icon: Store, path: '/store', color: 'from-emerald-500 to-emerald-600' },
  { name: 'NFT Market', icon: Store, path: '/marketplace', color: 'from-green-500 to-green-600' },
  { name: 'Health', icon: Heart, path: '/health', color: 'from-red-500 to-red-600' },
  { name: 'Games', icon: Gamepad2, path: '/games', color: 'from-yellow-500 to-yellow-600' },
  { name: 'Subscription', icon: Settings, path: '/subscription', color: 'from-pink-500 to-pink-600' },
  { name: 'AI Analysis', icon: Brain, path: '/ai', color: 'from-indigo-500 to-indigo-600' },
];

// Mock user data
const user = {
  name: 'Cat Lover',
  email: 'catlover@purrr.love',
  avatar: '🐱',
  level: 25,
  experience: 1250,
  nextLevel: 1500,
  notifications: 3
};

const App: React.FC = () => {
  return (
    <ErrorBoundary
      onError={(error, errorInfo) => {
        console.error('Global error caught:', error, errorInfo);
        // Send to error reporting service in production
      }}
    >
      <QueryProvider>
        <Web3Provider>
          <ThemeProvider>
            <Router>
              <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
                <AppLayout />
              </div>
            </Router>
          </ThemeProvider>
        </Web3Provider>
      </QueryProvider>
    </ErrorBoundary>
  );
};

const AppLayout: React.FC = () => {
  const [sidebarOpen, setSidebarOpen] = React.useState(false);
  const location = useLocation();
  
  // Cleanup on unmount
  React.useEffect(() => {
    return () => {
      // Cleanup any event listeners or subscriptions
      setSidebarOpen(false);
    };
  }, []);

  return (
    <div className="flex h-screen">
      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <motion.div
          className="fixed inset-0 z-40 lg:hidden"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          onClick={() => setSidebarOpen(false)}
        >
          <div className="absolute inset-0 bg-gray-600 opacity-75"></div>
        </motion.div>
      )}

      {/* Sidebar */}
      <motion.div
        className={`fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 ${
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
        initial={{ x: -256 }}
        animate={{ x: sidebarOpen ? 0 : -256 }}
        transition={{ type: "spring", stiffness: 300, damping: 30 }}
      >
        <div className="flex flex-col h-full">
          {/* Logo */}
          <div className="flex items-center justify-between p-6 border-b border-gray-200">
            <div className="flex items-center space-x-3">
              <div className="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center text-white text-xl font-bold">
                🐱
              </div>
              <div>
                <h1 className="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                  Purrr.love
                </h1>
                <p className="text-xs text-gray-500">Cat Gaming Ecosystem</p>
              </div>
            </div>
            <button
              onClick={() => setSidebarOpen(false)}
              className="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          {/* User Profile */}
          <div className="p-6 border-b border-gray-200">
            <div className="flex items-center space-x-3">
              <div className="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-xl">
                {user.avatar}
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 truncate">{user.name}</p>
                <p className="text-xs text-gray-500 truncate">{user.email}</p>
                <div className="mt-2">
                  <div className="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span>Level {user.level}</span>
                    <span>{user.experience}/{user.nextLevel} XP</span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      className="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full transition-all duration-300"
                      style={{ width: `${(user.experience / user.nextLevel) * 100}%` }}
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Navigation */}
          <nav className="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            {navigationItems.map((item) => {
              const isActive = location.pathname === item.path;
              const Icon = item.icon;
              
              return (
                <Link
                  key={item.name}
                  to={item.path}
                  onClick={() => setSidebarOpen(false)}
                  className={`group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 ${
                    isActive
                      ? `bg-gradient-to-r ${item.color} text-white shadow-lg`
                      : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                  }`}
                >
                  <Icon className={`mr-3 w-5 h-5 ${
                    isActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-500'
                  }`} />
                  {item.name}
                </Link>
              );
            })}
          </nav>

          {/* Logout */}
          <div className="p-4 border-t border-gray-200">
            <button className="flex items-center w-full px-3 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200">
              <LogOut className="mr-3 w-5 h-5 text-gray-400" />
              Logout
            </button>
          </div>
        </div>
      </motion.div>

      {/* Main content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Top bar */}
        <header className="bg-white shadow-sm border-b border-gray-200 z-30">
          <div className="flex items-center justify-between px-6 py-4">
            <div className="flex items-center space-x-4">
              <button
                onClick={() => setSidebarOpen(true)}
                className="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200"
              >
                <Menu className="w-6 h-6" />
              </button>
              
              <div className="hidden md:block">
                <h2 className="text-xl font-semibold text-gray-900">
                  {navigationItems.find(item => item.path === location.pathname)?.name || 'Dashboard'}
                </h2>
              </div>
            </div>

            <div className="flex items-center space-x-4">
              {/* Search */}
              <div className="hidden md:block relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                <input
                  type="text"
                  placeholder="Search..."
                  className="w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                />
              </div>

              {/* Notifications */}
              <button className="relative p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                <Bell className="w-6 h-6 text-gray-600" />
                {user.notifications > 0 && (
                  <span className="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                    {user.notifications}
                  </span>
                )}
              </button>

              {/* User menu */}
              <button className="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                <div className="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-sm">
                  {user.avatar}
                </div>
                <span className="hidden md:block text-sm font-medium text-gray-700">{user.name}</span>
                <User className="w-4 h-4 text-gray-400" />
              </button>
            </div>
          </div>
        </header>

        {/* Page content */}
        <main className="flex-1 overflow-y-auto">
          <AnimatePresence mode="wait">
            <motion.div
              key={location.pathname}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -20 }}
              transition={{ duration: 0.3 }}
            >
              <Routes>
                <Route path="/" element={<Dashboard />} />
                <Route path="/cats" element={<CatManagement />} />
                <Route path="/store" element={<VirtualStore />} />
                <Route path="/marketplace" element={<NFTMarketplace />} />
                <Route path="/subscription" element={<Subscription />} />
                <Route path="/health" element={<HealthPage />} />
                <Route path="/games" element={<GamesPage />} />
                <Route path="/ai" element={<AIPage />} />
              </Routes>
            </motion.div>
          </AnimatePresence>
        </main>
      </div>
    </div>
  );
};

// Placeholder page components
const Dashboard: React.FC = () => (
  <div className="p-8">
    <h1 className="text-3xl font-bold text-gray-900 mb-6">Dashboard</h1>
    <p className="text-gray-600">Welcome to your Purrr.love dashboard!</p>
  </div>
);

const HealthPage: React.FC = () => (
  <div className="p-8">
    <h1 className="text-3xl font-bold text-gray-900 mb-6">Health Monitoring</h1>
    <p className="text-gray-600">Monitor your cats' health and wellness.</p>
  </div>
);

const GamesPage: React.FC = () => (
  <div className="p-8">
    <h1 className="text-3xl font-bold text-gray-900 mb-6">Games & Entertainment</h1>
    <p className="text-gray-600">Play games with your cats and earn rewards.</p>
  </div>
);

const StorePage: React.FC = () => (
  <div className="p-8">
    <h1 className="text-3xl font-bold text-gray-900 mb-6">Store</h1>
    <p className="text-gray-600">Shop for cat items, toys, and accessories.</p>
  </div>
);

const AIPage: React.FC = () => (
  <div className="p-8">
    <h1 className="text-3xl font-bold text-gray-900 mb-6">AI Analysis</h1>
    <p className="text-gray-600">Get AI-powered insights about your cats.</p>
  </div>
);

const SettingsPage: React.FC = () => (
  <div className="p-8">
    <h1 className="text-3xl font-bold text-gray-900 mb-6">Settings</h1>
    <p className="text-gray-600">Manage your account and preferences.</p>
  </div>
);

export default App;
