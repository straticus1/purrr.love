import React from 'react';
import {createBottomTabNavigator} from '@react-navigation/bottom-tabs';
import {Platform} from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';

import {Colors} from '@/utils/Colors';

// Import screen navigators
import {HomeStackNavigator} from './HomeStackNavigator';
import {CatsStackNavigator} from './CatsStackNavigator';
import {StoreStackNavigator} from './StoreStackNavigator';
import {GamesStackNavigator} from './GamesStackNavigator';
import {ProfileStackNavigator} from './ProfileStackNavigator';

export type MainTabParamList = {
  HomeTab: undefined;
  CatsTab: undefined;
  StoreTab: undefined;
  GamesTab: undefined;
  ProfileTab: undefined;
};

const Tab = createBottomTabNavigator<MainTabParamList>();

export const MainTabNavigator: React.FC = () => {
  return (
    <Tab.Navigator
      screenOptions={({route}) => ({
        headerShown: false,
        tabBarIcon: ({focused, color, size}) => {
          let iconName: string;

          switch (route.name) {
            case 'HomeTab':
              iconName = 'home';
              break;
            case 'CatsTab':
              iconName = 'pets';
              break;
            case 'StoreTab':
              iconName = 'store';
              break;
            case 'GamesTab':
              iconName = 'games';
              break;
            case 'ProfileTab':
              iconName = 'person';
              break;
            default:
              iconName = 'help';
          }

          return <Icon name={iconName} size={size} color={color} />;
        },
        tabBarActiveTintColor: Colors.brand.primary,
        tabBarInactiveTintColor: Colors.light.textSecondary,
        tabBarStyle: {
          backgroundColor: Colors.light.surface,
          borderTopWidth: 1,
          borderTopColor: Colors.light.border,
          paddingTop: Platform.OS === 'ios' ? 0 : 8,
          paddingBottom: Platform.OS === 'ios' ? 25 : 8,
          height: Platform.OS === 'ios' ? 85 : 65,
        },
        tabBarLabelStyle: {
          fontSize: 12,
          fontWeight: '500',
        },
      })}
    >
      <Tab.Screen
        name="HomeTab"
        component={HomeStackNavigator}
        options={{
          tabBarLabel: 'Home',
        }}
      />
      <Tab.Screen
        name="CatsTab"
        component={CatsStackNavigator}
        options={{
          tabBarLabel: 'My Cats',
        }}
      />
      <Tab.Screen
        name="StoreTab"
        component={StoreStackNavigator}
        options={{
          tabBarLabel: 'Store',
        }}
      />
      <Tab.Screen
        name="GamesTab"
        component={GamesStackNavigator}
        options={{
          tabBarLabel: 'Games',
        }}
      />
      <Tab.Screen
        name="ProfileTab"
        component={ProfileStackNavigator}
        options={{
          tabBarLabel: 'Profile',
        }}
      />
    </Tab.Navigator>
  );
};