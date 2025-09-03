"""
🐱 Purrr.love Python SDK - Main Client
Main client class for interacting with the Purrr.love API
"""

import requests
import json
from typing import Dict, List, Optional, Union, Any
from urllib.parse import urljoin

from .exceptions import PurrrLoveError, AuthenticationError, RateLimitError
from .models import Cat, User, ApiKey, TradingOffer, CatShow


class PurrrLoveClient:
    """
    Main client for interacting with the Purrr.love API
    """
    
    def __init__(self, base_url: str = "https://api.purrr.love", api_key: Optional[str] = None):
        """
        Initialize the Purrr.love client
        
        Args:
            base_url: Base URL for the API
            api_key: API key for authentication
        """
        self.base_url = base_url.rstrip('/')
        self.api_key = api_key
        self.session = requests.Session()
        
        # Set default headers
        self.session.headers.update({
            'User-Agent': f'PurrrLove-Python-SDK/{__version__}',
            'Content-Type': 'application/json'
        })
        
        if api_key:
            self.session.headers['X-API-Key'] = api_key
    
    def authenticate(self, api_key: str) -> None:
        """
        Authenticate with an API key
        
        Args:
            api_key: API key for authentication
        """
        self.api_key = api_key
        self.session.headers['X-API-Key'] = api_key
    
    def _make_request(self, method: str, endpoint: str, data: Optional[Dict] = None, 
                     params: Optional[Dict] = None) -> Dict[str, Any]:
        """
        Make a request to the API
        
        Args:
            method: HTTP method
            endpoint: API endpoint
            data: Request data
            params: Query parameters
            
        Returns:
            API response data
            
        Raises:
            AuthenticationError: If authentication fails
            RateLimitError: If rate limit is exceeded
            PurrrLoveError: For other API errors
        """
        url = urljoin(self.base_url, endpoint)
        
        try:
            response = self.session.request(
                method=method,
                url=url,
                json=data,
                params=params
            )
            
            # Handle rate limiting
            if response.status_code == 429:
                retry_after = response.headers.get('Retry-After', 60)
                raise RateLimitError(f"Rate limit exceeded. Retry after {retry_after} seconds")
            
            # Handle authentication errors
            if response.status_code == 401:
                raise AuthenticationError("Invalid API key or authentication failed")
            
            # Handle other errors
            if response.status_code >= 400:
                error_data = response.json() if response.content else {}
                error_message = error_data.get('error', {}).get('message', 'Unknown error')
                raise PurrrLoveError(f"API error {response.status_code}: {error_message}")
            
            # Parse response
            if response.content:
                return response.json()
            return {}
            
        except requests.exceptions.RequestException as e:
            raise PurrrLoveError(f"Request failed: {str(e)}")
    
    # Cat Management
    def get_cats(self, limit: int = 50, offset: int = 0) -> List[Cat]:
        """
        Get user's cats
        
        Args:
            limit: Maximum number of cats to return
            offset: Number of cats to skip
            
        Returns:
            List of Cat objects
        """
        params = {'limit': limit, 'offset': offset}
        response = self._make_request('GET', '/api/v1/cats', params=params)
        
        cats = []
        for cat_data in response.get('data', []):
            cats.append(Cat.from_dict(cat_data))
        
        return cats
    
    def get_cat(self, cat_id: int) -> Cat:
        """
        Get a specific cat by ID
        
        Args:
            cat_id: ID of the cat to retrieve
            
        Returns:
            Cat object
        """
        response = self._make_request('GET', f'/api/v1/cats/{cat_id}')
        return Cat.from_dict(response['data'])
    
    def create_cat(self, name: str, species: str, personality_type: str, 
                   breed: str = 'mixed') -> Cat:
        """
        Create a new cat
        
        Args:
            name: Cat's name
            species: Cat's species
            personality_type: Cat's personality type
            breed: Cat's breed
            
        Returns:
            Created Cat object
        """
        data = {
            'name': name,
            'species': species,
            'personality_type': personality_type,
            'breed': breed
        }
        
        response = self._make_request('POST', '/api/v1/cats', data=data)
        return Cat.from_dict(response['data'])
    
    def update_cat(self, cat_id: int, **kwargs) -> Cat:
        """
        Update a cat's information
        
        Args:
            cat_id: ID of the cat to update
            **kwargs: Fields to update
            
        Returns:
            Updated Cat object
        """
        response = self._make_request('PUT', f'/api/v1/cats/{cat_id}', data=kwargs)
        return Cat.from_dict(response['data'])
    
    def delete_cat(self, cat_id: int) -> bool:
        """
        Delete a cat
        
        Args:
            cat_id: ID of the cat to delete
            
        Returns:
            True if successful
        """
        self._make_request('DELETE', f'/api/v1/cats/{cat_id}')
        return True
    
    # Cat Activities
    def play_with_cat(self, cat_id: int, game_type: str, duration: int = 10) -> Dict[str, Any]:
        """
        Play with a cat
        
        Args:
            cat_id: ID of the cat
            game_type: Type of game to play
            duration: Duration of play session in minutes
            
        Returns:
            Play session results
        """
        data = {
            'game_type': game_type,
            'duration': duration
        }
        
        response = self._make_request('POST', f'/api/v1/cats/{cat_id}/play', data=data)
        return response['data']
    
    def train_cat(self, cat_id: int, command: str, difficulty: str = 'normal') -> Dict[str, Any]:
        """
        Train a cat
        
        Args:
            cat_id: ID of the cat
            command: Training command
            difficulty: Difficulty level
            
        Returns:
            Training results
        """
        data = {
            'command': command,
            'difficulty': difficulty
        }
        
        response = self._make_request('POST', f'/api/v1/cats/{cat_id}/train', data=data)
        return response['data']
    
    def care_for_cat(self, cat_id: int, care_type: str, **kwargs) -> Dict[str, Any]:
        """
        Care for a cat
        
        Args:
            cat_id: ID of the cat
            care_type: Type of care
            **kwargs: Additional care parameters
            
        Returns:
            Care results
        """
        data = {'care_type': care_type, **kwargs}
        response = self._make_request('POST', f'/api/v1/cats/{cat_id}/care', data=data)
        return response['data']
    
    # VR Interactions
    def start_vr_session(self, cat_id: int, vr_device: str = 'webvr') -> Dict[str, Any]:
        """
        Start a VR interaction session
        
        Args:
            cat_id: ID of the cat
            vr_device: VR device type
            
        Returns:
            VR session data
        """
        data = {'vr_device': vr_device}
        response = self._make_request('POST', f'/api/v1/cats/{cat_id}/vr_interaction', data=data)
        return response['data']
    
    def vr_interact(self, session_id: str, interaction_type: str, **kwargs) -> Dict[str, Any]:
        """
        Perform VR interaction
        
        Args:
            session_id: VR session ID
            interaction_type: Type of interaction
            **kwargs: Interaction parameters
            
        Returns:
            Interaction results
        """
        data = {'interaction_type': interaction_type, **kwargs}
        response = self._make_request('POST', f'/api/v1/vr/{session_id}/interact', data=data)
        return response['data']
    
    # AI Learning
    def get_ai_insights(self, cat_id: int) -> Dict[str, Any]:
        """
        Get AI learning insights for a cat
        
        Args:
            cat_id: ID of the cat
            
        Returns:
            AI learning insights
        """
        response = self._make_request('GET', f'/api/v1/cats/{cat_id}/ai_learning')
        return response['data']
    
    # Trading
    def get_trading_offers(self, filters: Optional[Dict] = None) -> List[TradingOffer]:
        """
        Get available trading offers
        
        Args:
            filters: Optional filters for offers
            
        Returns:
            List of trading offers
        """
        response = self._make_request('GET', '/api/v1/trading/offers', params=filters)
        
        offers = []
        for offer_data in response.get('data', []):
            offers.append(TradingOffer.from_dict(offer_data))
        
        return offers
    
    def create_trading_offer(self, cat_id: int, price: float, description: str = '',
                            currency: str = 'USD') -> TradingOffer:
        """
        Create a trading offer
        
        Args:
            cat_id: ID of the cat to trade
            price: Price for the cat
            description: Offer description
            currency: Currency for the price
            
        Returns:
            Created trading offer
        """
        data = {
            'cat_id': cat_id,
            'price': price,
            'description': description,
            'currency': currency
        }
        
        response = self._make_request('POST', '/api/v1/trading/offers', data=data)
        return TradingOffer.from_dict(response['data'])
    
    def accept_trading_offer(self, offer_id: int) -> Dict[str, Any]:
        """
        Accept a trading offer
        
        Args:
            offer_id: ID of the offer to accept
            
        Returns:
            Trade completion data
        """
        response = self._make_request('POST', f'/api/v1/trading/offers/{offer_id}/accept')
        return response['data']
    
    # Cat Shows
    def get_cat_shows(self, filters: Optional[Dict] = None) -> List[CatShow]:
        """
        Get available cat shows
        
        Args:
            filters: Optional filters for shows
            
        Returns:
            List of cat shows
        """
        response = self._make_request('GET', '/api/v1/shows', params=filters)
        
        shows = []
        for show_data in response.get('data', []):
            shows.append(CatShow.from_dict(show_data))
        
        return shows
    
    def register_cat_for_show(self, cat_id: int, show_id: int, 
                             categories: List[str]) -> Dict[str, Any]:
        """
        Register a cat for a show
        
        Args:
            cat_id: ID of the cat
            show_id: ID of the show
            categories: Categories to register for
            
        Returns:
            Registration confirmation
        """
        data = {
            'cat_id': cat_id,
            'show_id': show_id,
            'categories': categories
        }
        
        response = self._make_request('POST', f'/api/v1/shows/{show_id}/register', data=data)
        return response['data']
    
    # Multiplayer
    def join_multiplayer_room(self, cat_id: int, room_type: str = 'playground') -> Dict[str, Any]:
        """
        Join a multiplayer room
        
        Args:
            cat_id: ID of the cat
            room_type: Type of room to join
            
        Returns:
            Multiplayer session data
        """
        data = {
            'cat_id': cat_id,
            'room_type': room_type
        }
        
        response = self._make_request('POST', f'/api/v1/multiplayer/join', data=data)
        return response['data']
    
    def multiplayer_action(self, session_id: str, action_type: str, **kwargs) -> Dict[str, Any]:
        """
        Perform multiplayer action
        
        Args:
            session_id: Multiplayer session ID
            action_type: Type of action
            **kwargs: Action parameters
            
        Returns:
            Action results
        """
        data = {'action_type': action_type, **kwargs}
        response = self._make_request('POST', f'/api/v1/multiplayer/{session_id}/action', data=data)
        return response['data']
    
    # Health Monitoring
    def register_health_device(self, cat_id: int, device_data: Dict) -> Dict[str, Any]:
        """
        Register a health monitoring device
        
        Args:
            cat_id: ID of the cat
            device_data: Device information
            
        Returns:
            Device registration data
        """
        response = self._make_request('POST', f'/api/v1/cats/{cat_id}/health_monitoring', data=device_data)
        return response['data']
    
    def get_health_summary(self, cat_id: int, timeframe: str = '7d') -> Dict[str, Any]:
        """
        Get cat health summary
        
        Args:
            cat_id: ID of the cat
            timeframe: Timeframe for health data
            
        Returns:
            Health summary data
        """
        params = {'timeframe': timeframe}
        response = self._make_request('GET', f'/api/v1/cats/{cat_id}/health', params=params)
        return response['data']
    
    # API Key Management
    def get_api_keys(self) -> List[ApiKey]:
        """
        Get user's API keys
        
        Returns:
            List of API keys
        """
        response = self._make_request('GET', '/api/v1/keys')
        
        keys = []
        for key_data in response.get('data', []):
            keys.append(ApiKey.from_dict(key_data))
        
        return keys
    
    def create_api_key(self, name: str, scopes: List[str], 
                       expires_at: Optional[str] = None) -> ApiKey:
        """
        Create a new API key
        
        Args:
            name: Name for the API key
            scopes: Permissions for the key
            expires_at: Optional expiration date
            
        Returns:
            Created API key
        """
        data = {
            'name': name,
            'scopes': scopes
        }
        
        if expires_at:
            data['expires_at'] = expires_at
        
        response = self._make_request('POST', '/api/v1/keys', data=data)
        return ApiKey.from_dict(response['data'])
    
    def revoke_api_key(self, key_id: int) -> bool:
        """
        Revoke an API key
        
        Args:
            key_id: ID of the key to revoke
            
        Returns:
            True if successful
        """
        self._make_request('DELETE', f'/api/v1/keys/{key_id}')
        return True
    
    # Analytics
    def get_cat_analytics(self, cat_id: int, timeframe: str = '30d', 
                          metrics: Optional[List[str]] = None) -> Dict[str, Any]:
        """
        Get cat analytics
        
        Args:
            cat_id: ID of the cat
            timeframe: Timeframe for analytics
            metrics: Specific metrics to retrieve
            
        Returns:
            Analytics data
        """
        params = {'timeframe': timeframe}
        if metrics:
            params['metrics'] = metrics
        
        response = self._make_request('GET', f'/api/v1/cats/{cat_id}/analytics', params=params)
        return response['data']
    
    def get_user_stats(self) -> Dict[str, Any]:
        """
        Get user statistics
        
        Returns:
            User statistics
        """
        response = self._make_request('GET', '/api/v1/user/stats')
        return response['data']
    
    def close(self):
        """
        Close the client session
        """
        self.session.close()
    
    def __enter__(self):
        return self
    
    def __exit__(self, exc_type, exc_val, exc_tb):
        self.close()
