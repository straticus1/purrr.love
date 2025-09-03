/**
 * 🐱 Purrr.love Node.js SDK
 * Official Node.js client library for the Purrr.love cat gaming platform
 */

export { PurrrLoveClient } from './client/PurrrLoveClient';
export { PurrrLoveWebSocketClient } from './client/PurrrLoveWebSocketClient';

// Models
export { Cat } from './models/Cat';
export { User } from './models/User';
export { ApiKey } from './models/ApiKey';
export { TradingOffer } from './models/TradingOffer';
export { CatShow } from './models/CatShow';
export { VRInteraction } from './models/VRInteraction';
export { HealthDevice } from './models/HealthDevice';
export { MultiplayerSession } from './models/MultiplayerSession';

// Enums
export { PersonalityType } from './models/enums/PersonalityType';
export { MoodState } from './models/enums/MoodState';
export { CatBreed } from './models/enums/CatBreed';
export { GameType } from './models/enums/GameType';
export { CareType } from './models/enums/CareType';

// Exceptions
export { PurrrLoveError } from './exceptions/PurrrLoveError';
export { AuthenticationError } from './exceptions/AuthenticationError';
export { RateLimitError } from './exceptions/RateLimitError';
export { ValidationError } from './exceptions/ValidationError';
export { NotFoundError } from './exceptions/NotFoundError';
export { PermissionError } from './exceptions/PermissionError';
export { ConflictError } from './exceptions/ConflictError';
export { ServerError } from './exceptions/ServerError';
export { NetworkError } from './exceptions/NetworkError';
export { TimeoutError } from './exceptions/TimeoutError';

// Types
export type { 
  CatData, 
  UserData, 
  ApiKeyData, 
  TradingOfferData, 
  CatShowData,
  VRInteractionData,
  HealthDeviceData,
  MultiplayerSessionData,
  ClientConfig,
  RequestOptions,
  ResponseData
} from './types';

// Constants
export { API_ENDPOINTS, DEFAULT_TIMEOUT, MAX_RETRIES } from './constants';

// Version
export const VERSION = '2.0.0';
export const SDK_NAME = 'PurrrLove-NodeJS-SDK';
