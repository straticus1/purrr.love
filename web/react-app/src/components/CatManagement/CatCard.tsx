import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Heart, Gamepad, Edit, Trash2, Activity, Zap, Droplets, Star } from 'lucide-react';
import { Cat } from '@/types/cat';

interface CatCardProps {
  cat: Cat;
  onEdit: (cat: Cat) => void;
  onDelete: (catId: string) => void;
  onCare: (catId: string) => void;
  onPlay: (catId: string) => void;
}

const CatCard: React.FC<CatCardProps> = ({
  cat,
  onEdit,
  onDelete,
  onCare,
  onPlay,
}) => {
  const [isHovered, setIsHovered] = useState(false);
  const [showActions, setShowActions] = useState(false);

  const getHealthColor = (health: number) => {
    if (health >= 80) return 'text-green-500';
    if (health >= 60) return 'text-yellow-500';
    return 'text-red-500';
  };

  const getStatBarColor = (value: number, type: 'health' | 'happiness' | 'energy' | 'hunger') => {
    const colors = {
      health: value >= 80 ? 'bg-green-500' : value >= 60 ? 'bg-yellow-500' : 'bg-red-500',
      happiness: value >= 80 ? 'bg-blue-500' : value >= 60 ? 'bg-orange-500' : 'bg-red-500',
      energy: value >= 80 ? 'bg-purple-500' : value >= 60 ? 'bg-yellow-500' : 'bg-red-500',
      hunger: value >= 80 ? 'bg-green-500' : value >= 60 ? 'bg-orange-500' : 'bg-red-500',
    };
    return colors[type];
  };

  const getCatAvatar = (name: string) => {
    return name.charAt(0).toUpperCase();
  };

  return (
    <motion.div
      className="relative group"
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5 }}
      onHoverStart={() => setIsHovered(true)}
      onHoverEnd={() => setIsHovered(false)}
    >
      <motion.div
        className="bg-white rounded-3xl p-6 shadow-xl border border-gray-100 cursor-pointer overflow-hidden"
        whileHover={{ 
          y: -8, 
          scale: 1.02,
          boxShadow: "0 25px 50px -12px rgba(0, 0, 0, 0.25)"
        }}
        transition={{ type: "spring", stiffness: 300, damping: 20 }}
      >
        {/* Background Pattern */}
        <div className="absolute inset-0 bg-gradient-to-br from-purple-50 to-pink-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
        
        {/* Header */}
        <div className="relative flex items-center justify-between mb-6">
          <div className="flex items-center space-x-4">
            <motion.div
              className="w-20 h-20 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-lg"
              style={{
                background: `linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%)`
              }}
              whileHover={{ rotate: 360 }}
              transition={{ duration: 0.6 }}
            >
              {getCatAvatar(cat.name)}
            </motion.div>
            <div>
              <h3 className="text-xl font-bold text-gray-900 group-hover:text-purple-600 transition-colors">
                {cat.name}
              </h3>
              <p className="text-sm text-gray-500">
                {cat.breed || 'Mixed Breed'}
              </p>
            </div>
          </div>
          
          {/* Action Menu */}
          <div className="relative">
            <motion.button
              className="p-2 text-gray-400 hover:text-purple-600 transition-colors rounded-full hover:bg-purple-50"
              onClick={() => setShowActions(!showActions)}
              whileTap={{ scale: 0.95 }}
            >
              <Edit size={16} />
            </motion.button>
            
            <AnimatePresence>
              {showActions && (
                <motion.div
                  className="absolute right-0 top-10 bg-white rounded-2xl shadow-xl border border-gray-100 p-2 z-10"
                  initial={{ opacity: 0, scale: 0.95, y: -10 }}
                  animate={{ opacity: 1, scale: 1, y: 0 }}
                  exit={{ opacity: 0, scale: 0.95, y: -10 }}
                  transition={{ duration: 0.2 }}
                >
                  <button
                    className="flex items-center space-x-2 w-full px-3 py-2 text-sm text-gray-700 hover:bg-purple-50 rounded-lg transition-colors"
                    onClick={() => onEdit(cat)}
                  >
                    <Edit size={14} />
                    <span>Edit</span>
                  </button>
                  <button
                    className="flex items-center space-x-2 w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    onClick={() => onDelete(cat.id)}
                  >
                    <Trash2 size={14} />
                    <span>Delete</span>
                  </button>
                </motion.div>
              )}
            </AnimatePresence>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-2 gap-4 mb-6">
          <div className="text-center">
            <div className="flex items-center justify-center mb-2">
              <Activity className="w-4 h-4 text-green-500 mr-1" />
              <span className="text-sm font-medium text-gray-600">Health</span>
            </div>
            <div className="text-2xl font-bold text-gray-900">{cat.health}%</div>
          </div>
          
          <div className="text-center">
            <div className="flex items-center justify-center mb-2">
              <Star className="w-4 h-4 text-blue-500 mr-1" />
              <span className="text-sm font-medium text-gray-600">Happiness</span>
            </div>
            <div className="text-2xl font-bold text-gray-900">{cat.happiness}%</div>
          </div>
          
          <div className="text-center">
            <div className="flex items-center justify-center mb-2">
              <Zap className="w-4 h-4 text-purple-500 mr-1" />
              <span className="text-sm font-medium text-gray-600">Energy</span>
            </div>
            <div className="text-2xl font-bold text-gray-900">{cat.energy}%</div>
          </div>
          
          <div className="text-center">
            <div className="flex items-center justify-center mb-2">
              <Droplets className="w-4 h-4 text-orange-500 mr-1" />
              <span className="text-sm font-medium text-gray-600">Hunger</span>
            </div>
            <div className="text-2xl font-bold text-gray-900">{cat.hunger}%</div>
          </div>
        </div>

        {/* Progress Bars */}
        <div className="space-y-3 mb-6">
          <div>
            <div className="flex justify-between text-xs text-gray-500 mb-1">
              <span>Health</span>
              <span>{cat.health}%</span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <motion.div
                className={`h-full rounded-full ${getStatBarColor(cat.health, 'health')}`}
                initial={{ width: 0 }}
                animate={{ width: `${cat.health}%` }}
                transition={{ duration: 1, delay: 0.2 }}
              />
            </div>
          </div>
          
          <div>
            <div className="flex justify-between text-xs text-gray-500 mb-1">
              <span>Happiness</span>
              <span>{cat.happiness}%</span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <motion.div
                className={`h-full rounded-full ${getStatBarColor(cat.happiness, 'happiness')}`}
                initial={{ width: 0 }}
                animate={{ width: `${cat.happiness}%` }}
                transition={{ duration: 1, delay: 0.3 }}
              />
            </div>
          </div>
          
          <div>
            <div className="flex justify-between text-xs text-gray-500 mb-1">
              <span>Energy</span>
              <span>{cat.energy}%</span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <motion.div
                className={`h-full rounded-full ${getStatBarColor(cat.energy, 'energy')}`}
                initial={{ width: 0 }}
                animate={{ width: `${cat.energy}%` }}
                transition={{ duration: 1, delay: 0.4 }}
              />
            </div>
          </div>
          
          <div>
            <div className="flex justify-between text-xs text-gray-500 mb-1">
              <span>Hunger</span>
              <span>{cat.hunger}%</span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <motion.div
                className={`h-full rounded-full ${getStatBarColor(cat.hunger, 'hunger')}`}
                initial={{ width: 0 }}
                animate={{ width: `${cat.hunger}%` }}
                transition={{ duration: 1, delay: 0.5 }}
              />
            </div>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-2 gap-3">
          <motion.button
            className="flex items-center justify-center space-x-2 bg-purple-100 text-purple-700 px-4 py-3 rounded-xl hover:bg-purple-200 transition-colors text-sm font-medium"
            onClick={() => onCare(cat.id)}
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
          >
            <Heart size={16} />
            <span>Care</span>
          </motion.button>
          
          <motion.button
            className="flex items-center justify-center space-x-2 bg-blue-100 text-blue-700 px-4 py-3 rounded-xl hover:bg-blue-200 transition-colors text-sm font-medium"
            onClick={() => onPlay(cat.id)}
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
          >
            <Gamepad size={16} />
            <span>Play</span>
          </motion.button>
        </div>

        {/* Cat Info */}
        <div className="mt-4 pt-4 border-t border-gray-100">
          <div className="flex justify-between text-xs text-gray-500">
            <span>Age: {cat.age || 'Unknown'}</span>
            <span>Created: {new Date(cat.createdAt).toLocaleDateString()}</span>
          </div>
        </div>

        {/* Hover Effects */}
        <motion.div
          className="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-pink-500/5 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"
          style={{ pointerEvents: 'none' }}
        />
      </motion.div>

      {/* Floating Elements */}
      <AnimatePresence>
        {isHovered && (
          <>
            <motion.div
              className="absolute -top-2 -right-2 w-4 h-4 bg-purple-500 rounded-full"
              initial={{ scale: 0, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0, opacity: 0 }}
              transition={{ duration: 0.3 }}
            />
            <motion.div
              className="absolute -bottom-2 -left-2 w-3 h-3 bg-pink-500 rounded-full"
              initial={{ scale: 0, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0, opacity: 0 }}
              transition={{ duration: 0.3, delay: 0.1 }}
            />
          </>
        )}
      </AnimatePresence>
    </motion.div>
  );
};

export default CatCard;
