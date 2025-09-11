import React from 'react';
import {createNativeStackNavigator} from '@react-navigation/native-stack';
import {useSelector} from 'react-redux';

import {RootState} from '@/store';
import {AuthNavigator} from './AuthNavigator';
import {MainTabNavigator} from './MainTabNavigator';
import {LoadingScreen} from '@/components/common/LoadingScreen';

const Stack = createNativeStackNavigator();

export const AppNavigator: React.FC = () => {
  const {isAuthenticated, isLoading} = useSelector((state: RootState) => state.auth);

  if (isLoading) {
    return <LoadingScreen text="Initializing..." />;
  }

  return (
    <Stack.Navigator screenOptions={{headerShown: false}}>
      {isAuthenticated ? (
        <Stack.Screen name="Main" component={MainTabNavigator} />
      ) : (
        <Stack.Screen name="Auth" component={AuthNavigator} />
      )}
    </Stack.Navigator>
  );
};