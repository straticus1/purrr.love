import React from 'react';
import {createNativeStackNavigator} from '@react-navigation/native-stack';

// Import screens
import {HomeScreen} from '@/screens/home/HomeScreen';
import {NotificationsScreen} from '@/screens/home/NotificationsScreen';

export type HomeStackParamList = {
  Home: undefined;
  Notifications: undefined;
};

const Stack = createNativeStackNavigator<HomeStackParamList>();

export const HomeStackNavigator: React.FC = () => {
  return (
    <Stack.Navigator
      screenOptions={{
        headerShown: true,
        headerStyle: {
          backgroundColor: '#FFFFFF',
        },
        headerTintColor: '#0F172A',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    >
      <Stack.Screen
        name="Home"
        component={HomeScreen}
        options={{
          title: 'Purrr.love',
        }}
      />
      <Stack.Screen
        name="Notifications"
        component={NotificationsScreen}
        options={{
          title: 'Notifications',
        }}
      />
    </Stack.Navigator>
  );
};