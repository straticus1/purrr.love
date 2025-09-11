import PushNotification, {Importance} from 'react-native-push-notification';
import {Platform} from 'react-native';

class NotificationManagerClass {
  private initialized = false;

  initialize() {
    if (this.initialized) return;

    PushNotification.configure({
      onRegister: (token) => {
        console.log('Push notification token:', token);
        // Send token to your backend
        this.sendTokenToServer(token.token);
      },

      onNotification: (notification) => {
        console.log('Notification received:', notification);
        
        if (notification.userInteraction) {
          // User tapped the notification
          this.handleNotificationTap(notification);
        }
      },

      onAction: (notification) => {
        console.log('Notification action received:', notification);
      },

      onRegistrationError: (err) => {
        console.error('Push notification registration error:', err);
      },

      permissions: {
        alert: true,
        badge: true,
        sound: true,
      },

      popInitialNotification: true,
      requestPermissions: true,
    });

    this.createDefaultChannels();
    this.initialized = true;
  }

  private createDefaultChannels() {
    if (Platform.OS === 'android') {
      PushNotification.createChannel(
        {
          channelId: 'default-channel',
          channelName: 'Default',
          channelDescription: 'Default notifications',
          playSound: true,
          soundName: 'default',
          importance: Importance.HIGH,
          vibrate: true,
        },
        () => {},
      );

      PushNotification.createChannel(
        {
          channelId: 'cat-care-channel',
          channelName: 'Cat Care',
          channelDescription: 'Notifications about your cats needs',
          playSound: true,
          soundName: 'default',
          importance: Importance.HIGH,
          vibrate: true,
        },
        () => {},
      );

      PushNotification.createChannel(
        {
          channelId: 'game-updates-channel',
          channelName: 'Game Updates',
          channelDescription: 'Game achievements and updates',
          playSound: false,
          importance: Importance.DEFAULT,
          vibrate: false,
        },
        () => {},
      );
    }
  }

  private async sendTokenToServer(token: string) {
    try {
      // In a real app, send this to your backend
      console.log('Sending push token to server:', token);
      // await ApiService.updatePushToken(token);
    } catch (error) {
      console.error('Failed to send push token to server:', error);
    }
  }

  private handleNotificationTap(notification: any) {
    // Handle notification tap based on notification data
    console.log('User tapped notification:', notification);
    
    // Navigate to appropriate screen based on notification type
    // NavigationService.navigate('CatDetail', { catId: notification.data?.catId });
  }

  // Schedule local notification
  scheduleNotification(
    title: string,
    message: string,
    date: Date,
    channelId: string = 'default-channel',
    data?: any,
  ) {
    PushNotification.localNotificationSchedule({
      id: Date.now().toString(),
      title,
      message,
      date,
      channelId,
      userInfo: data,
      repeatType: undefined,
    });
  }

  // Send immediate local notification
  showNotification(
    title: string,
    message: string,
    channelId: string = 'default-channel',
    data?: any,
  ) {
    PushNotification.localNotification({
      id: Date.now().toString(),
      title,
      message,
      channelId,
      userInfo: data,
      playSound: true,
      soundName: 'default',
    });
  }

  // Cat care reminders
  scheduleCatFeedingReminder(catName: string, feedingTime: Date) {
    this.scheduleNotification(
      `Time to feed ${catName}! 🍽️`,
      `${catName} is getting hungry and needs some food.`,
      feedingTime,
      'cat-care-channel',
      { type: 'feeding', catName },
    );
  }

  scheduleCatPlayReminder(catName: string, playTime: Date) {
    this.scheduleNotification(
      `${catName} wants to play! 🎾`,
      `It's been a while since you played with ${catName}.`,
      playTime,
      'cat-care-channel',
      { type: 'play', catName },
    );
  }

  scheduleVetReminder(catName: string, appointmentTime: Date) {
    this.scheduleNotification(
      `Vet appointment reminder 🏥`,
      `Don't forget about ${catName}'s vet appointment.`,
      appointmentTime,
      'cat-care-channel',
      { type: 'vet', catName },
    );
  }

  // Game notifications
  showAchievementUnlocked(achievementName: string) {
    this.showNotification(
      'Achievement Unlocked! 🏆',
      `Congratulations! You earned: ${achievementName}`,
      'game-updates-channel',
      { type: 'achievement', achievement: achievementName },
    );
  }

  showLevelUp(newLevel: number) {
    this.showNotification(
      'Level Up! ⬆️',
      `Amazing! You've reached level ${newLevel}!`,
      'game-updates-channel',
      { type: 'levelUp', level: newLevel },
    );
  }

  // Clear notifications
  cancelNotification(id: string) {
    PushNotification.cancelLocalNotifications({ id });
  }

  cancelAllNotifications() {
    PushNotification.cancelAllLocalNotifications();
  }

  // Get notification permissions
  async checkPermissions(): Promise<any> {
    return new Promise((resolve) => {
      PushNotification.checkPermissions(resolve);
    });
  }

  async requestPermissions(): Promise<any> {
    return new Promise((resolve) => {
      PushNotification.requestPermissions().then(resolve);
    });
  }

  cleanup() {
    // Cleanup when app is closing
    this.initialized = false;
  }
}

export const NotificationManager = new NotificationManagerClass();