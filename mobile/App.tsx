import React from 'react';
import {StatusBar, useColorScheme} from 'react-native';
import {NavigationContainer} from '@react-navigation/native';
import {SafeAreaProvider} from 'react-native-safe-area-context';
import {GestureHandlerRootView} from 'react-native-gesture-handler';
import {Provider} from 'react-redux';
import {PersistGate} from 'redux-persist/integration/react';

import {store, persistor} from '@/store';
import {AppNavigator} from '@/navigation/AppNavigator';
import {Colors} from '@/utils/Colors';
import {LoadingScreen} from '@/components/common/LoadingScreen';
import {ErrorBoundary} from '@/components/common/ErrorBoundary';
import {NotificationManager} from '@/services/NotificationManager';

const App: React.FC = () => {
  const isDarkMode = useColorScheme() === 'dark';

  const backgroundStyle = {
    backgroundColor: isDarkMode ? Colors.dark.background : Colors.light.background,
    flex: 1,
  };

  React.useEffect(() => {
    // Initialize notification manager
    NotificationManager.initialize();
    
    return () => {
      // Cleanup on unmount
      NotificationManager.cleanup();
    };
  }, []);

  return (
    <ErrorBoundary>
      <Provider store={store}>
        <PersistGate loading={<LoadingScreen />} persistor={persistor}>
          <GestureHandlerRootView style={{flex: 1}}>
            <SafeAreaProvider>
              <NavigationContainer theme={{
                dark: isDarkMode,
                colors: {
                  primary: Colors.brand.primary,
                  background: isDarkMode ? Colors.dark.background : Colors.light.background,
                  card: isDarkMode ? Colors.dark.surface : Colors.light.surface,
                  text: isDarkMode ? Colors.dark.text : Colors.light.text,
                  border: isDarkMode ? Colors.dark.border : Colors.light.border,
                  notification: Colors.semantic.error,
                },
              }}>
                <StatusBar
                  barStyle={isDarkMode ? 'light-content' : 'dark-content'}
                  backgroundColor={backgroundStyle.backgroundColor}
                />
                <AppNavigator />
              </NavigationContainer>
            </SafeAreaProvider>
          </GestureHandlerRootView>
        </PersistGate>
      </Provider>
    </ErrorBoundary>
  );
};

export default App;