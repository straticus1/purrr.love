import React from 'react';
import {createNativeStackNavigator} from '@react-navigation/native-stack';

// Import screens
import {CatsListScreen} from '@/screens/cats/CatsListScreen';
import {CatDetailScreen} from '@/screens/cats/CatDetailScreen';
import {AddCatScreen} from '@/screens/cats/AddCatScreen';
import {CatCareScreen} from '@/screens/cats/CatCareScreen';

export type CatsStackParamList = {
  CatsList: undefined;
  CatDetail: {catId: string};
  AddCat: undefined;
  CatCare: {catId: string};
};

const Stack = createNativeStackNavigator<CatsStackParamList>();

export const CatsStackNavigator: React.FC = () => {
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
        name="CatsList"
        component={CatsListScreen}
        options={{
          title: 'My Cats',
        }}
      />
      <Stack.Screen
        name="CatDetail"
        component={CatDetailScreen}
        options={{
          title: 'Cat Details',
        }}
      />
      <Stack.Screen
        name="AddCat"
        component={AddCatScreen}
        options={{
          title: 'Add New Cat',
        }}
      />
      <Stack.Screen
        name="CatCare"
        component={CatCareScreen}
        options={{
          title: 'Cat Care',
        }}
      />
    </Stack.Navigator>
  );
};