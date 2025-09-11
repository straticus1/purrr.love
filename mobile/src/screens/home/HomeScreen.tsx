import React from 'react';
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  StyleSheet,
  Dimensions,
} from 'react-native';
import {useSelector} from 'react-redux';
import Icon from 'react-native-vector-icons/MaterialIcons';
import LinearGradient from 'react-native-linear-gradient';

import {RootState} from '@/store';
import {Colors} from '@/utils/Colors';

const {width} = Dimensions.get('window');

export const HomeScreen: React.FC = () => {
  const user = useSelector((state: RootState) => state.auth.user);

  const quickActions = [
    {
      id: 'feed',
      title: 'Feed Cats',
      icon: 'restaurant',
      color: Colors.semantic.success,
      description: 'Give your cats a meal',
    },
    {
      id: 'play',
      title: 'Play Games',
      icon: 'games',
      color: Colors.brand.secondary,
      description: 'Earn coins and XP',
    },
    {
      id: 'store',
      title: 'Visit Store',
      icon: 'store',
      color: Colors.brand.primary,
      description: 'Buy items and treats',
    },
    {
      id: 'health',
      title: 'Health Check',
      icon: 'favorite',
      color: Colors.semantic.error,
      description: 'Monitor cat health',
    },
  ];

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Welcome Header */}
      <LinearGradient
        colors={[Colors.brand.primary, Colors.brand.secondary]}
        style={styles.welcomeHeader}
        start={{x: 0, y: 0}}
        end={{x: 1, y: 1}}
      >
        <Text style={styles.welcomeText}>Welcome back!</Text>
        <Text style={styles.userName}>{user?.name || 'Cat Lover'}</Text>
        
        {/* User Stats */}
        <View style={styles.statsContainer}>
          <View style={styles.statItem}>
            <Text style={styles.statValue}>{user?.level || 1}</Text>
            <Text style={styles.statLabel}>Level</Text>
          </View>
          <View style={styles.statItem}>
            <Text style={styles.statValue}>{user?.coins || 0}</Text>
            <Text style={styles.statLabel}>Coins</Text>
          </View>
          <View style={styles.statItem}>
            <Text style={styles.statValue}>{user?.stats?.totalCats || 0}</Text>
            <Text style={styles.statLabel}>Cats</Text>
          </View>
        </View>
      </LinearGradient>

      {/* Quick Actions */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Quick Actions</Text>
        <View style={styles.quickActionsGrid}>
          {quickActions.map((action) => (
            <TouchableOpacity
              key={action.id}
              style={styles.quickActionCard}
              activeOpacity={0.7}
            >
              <View style={[styles.quickActionIcon, {backgroundColor: action.color}]}>
                <Icon name={action.icon} size={24} color="white" />
              </View>
              <Text style={styles.quickActionTitle}>{action.title}</Text>
              <Text style={styles.quickActionDescription}>{action.description}</Text>
            </TouchableOpacity>
          ))}
        </View>
      </View>

      {/* Today's Tasks */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Today's Tasks</Text>
        <View style={styles.tasksList}>
          <TouchableOpacity style={styles.taskItem}>
            <View style={styles.taskIconContainer}>
              <Icon name="restaurant" size={20} color={Colors.semantic.success} />
            </View>
            <View style={styles.taskContent}>
              <Text style={styles.taskTitle}>Feed Whiskers</Text>
              <Text style={styles.taskTime}>Due in 2 hours</Text>
            </View>
            <Icon name="chevron-right" size={20} color={Colors.light.textSecondary} />
          </TouchableOpacity>
          
          <TouchableOpacity style={styles.taskItem}>
            <View style={styles.taskIconContainer}>
              <Icon name="pets" size={20} color={Colors.brand.secondary} />
            </View>
            <View style={styles.taskContent}>
              <Text style={styles.taskTitle}>Play with Mittens</Text>
              <Text style={styles.taskTime}>Due in 4 hours</Text>
            </View>
            <Icon name="chevron-right" size={20} color={Colors.light.textSecondary} />
          </TouchableOpacity>

          <TouchableOpacity style={styles.taskItem}>
            <View style={styles.taskIconContainer}>
              <Icon name="medical-services" size={20} color={Colors.semantic.info} />
            </View>
            <View style={styles.taskContent}>
              <Text style={styles.taskTitle}>Vet Appointment</Text>
              <Text style={styles.taskTime}>Tomorrow at 3:00 PM</Text>
            </View>
            <Icon name="chevron-right" size={20} color={Colors.light.textSecondary} />
          </TouchableOpacity>
        </View>
      </View>

      {/* Recent Activity */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Recent Activity</Text>
        <View style={styles.activityList}>
          <View style={styles.activityItem}>
            <Text style={styles.activityText}>🎉 You reached level {user?.level || 1}!</Text>
            <Text style={styles.activityTime}>2 hours ago</Text>
          </View>
          <View style={styles.activityItem}>
            <Text style={styles.activityText}>🍽️ Fed Whiskers</Text>
            <Text style={styles.activityTime}>4 hours ago</Text>
          </View>
          <View style={styles.activityItem}>
            <Text style={styles.activityText}>🎮 Played Laser Chase - Score: 850</Text>
            <Text style={styles.activityTime}>Yesterday</Text>
          </View>
        </View>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.light.background,
  },
  welcomeHeader: {
    padding: 20,
    paddingTop: 40,
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24,
    marginBottom: 20,
  },
  welcomeText: {
    fontSize: 16,
    color: 'rgba(255, 255, 255, 0.8)',
    marginBottom: 4,
  },
  userName: {
    fontSize: 28,
    fontWeight: 'bold',
    color: 'white',
    marginBottom: 20,
  },
  statsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-around',
  },
  statItem: {
    alignItems: 'center',
  },
  statValue: {
    fontSize: 24,
    fontWeight: 'bold',
    color: 'white',
  },
  statLabel: {
    fontSize: 12,
    color: 'rgba(255, 255, 255, 0.8)',
    marginTop: 4,
  },
  section: {
    paddingHorizontal: 20,
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: Colors.light.text,
    marginBottom: 16,
  },
  quickActionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  quickActionCard: {
    width: (width - 60) / 2,
    backgroundColor: Colors.light.surface,
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    alignItems: 'center',
    shadowColor: Colors.alpha.black10,
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 1,
    shadowRadius: 8,
    elevation: 4,
  },
  quickActionIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  quickActionTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: Colors.light.text,
    marginBottom: 4,
  },
  quickActionDescription: {
    fontSize: 12,
    color: Colors.light.textSecondary,
    textAlign: 'center',
  },
  tasksList: {
    backgroundColor: Colors.light.surface,
    borderRadius: 16,
    overflow: 'hidden',
  },
  taskItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: Colors.light.border,
  },
  taskIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: Colors.light.surfaceVariant,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  taskContent: {
    flex: 1,
  },
  taskTitle: {
    fontSize: 16,
    fontWeight: '500',
    color: Colors.light.text,
    marginBottom: 2,
  },
  taskTime: {
    fontSize: 12,
    color: Colors.light.textSecondary,
  },
  activityList: {
    backgroundColor: Colors.light.surface,
    borderRadius: 16,
    overflow: 'hidden',
  },
  activityItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: Colors.light.border,
  },
  activityText: {
    fontSize: 14,
    color: Colors.light.text,
    flex: 1,
    marginRight: 12,
  },
  activityTime: {
    fontSize: 12,
    color: Colors.light.textSecondary,
  },
});