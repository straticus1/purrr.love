import React from 'react';
import {View, ActivityIndicator, Text, StyleSheet} from 'react-native';
import {Colors} from '@/utils/Colors';

interface LoadingScreenProps {
  text?: string;
}

export const LoadingScreen: React.FC<LoadingScreenProps> = ({
  text = 'Loading...',
}) => {
  return (
    <View style={styles.container}>
      <ActivityIndicator size="large" color={Colors.brand.primary} />
      <Text style={styles.text}>{text}</Text>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: Colors.light.background,
  },
  text: {
    marginTop: 16,
    fontSize: 16,
    color: Colors.light.textSecondary,
    fontWeight: '500',
  },
});