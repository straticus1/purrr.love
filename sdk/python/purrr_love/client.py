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

# Version constant
__version__ = "2.0.0"

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
        return response.get('data', {})
    
    def feed_cat(self, cat_id: int, food_type: str, amount: float = 1.0) -> Dict[str, Any]:
        """
        Feed a cat
        
        Args:
            cat_id: ID of the cat
            food_type: Type of food
            amount: Amount of food
            
        Returns:
            Feeding results
        """
        data = {
            'food_type': food_type,
            'amount': amount
        }
        
        response = self._make_request('POST', f'/api/v1/cats/{cat_id}/feed', data=data)
        return response.get('data', {})
    
    def groom_cat(self, cat_id: int, grooming_type: str) -> Dict[str, Any]:
        """
        Groom a cat
        
        Args:
            cat_id: ID of the cat
            grooming_type: Type of grooming
            
        Returns:
            Grooming results
        """
        data = {'grooming_type': grooming_type}
        response = self._make_request('POST', f'/api/v1/cats/{cat_id}/groom', data=data)
        return response.get('data', {})
    
    # Lost Pet Finder System
    def report_lost_pet(self, pet_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Report a lost pet
        
        Args:
            pet_data: Dictionary containing pet information
                - name: Pet's name
                - breed: Pet's breed
                - color: Pet's color
                - last_seen_location: Where pet was last seen
                - last_seen_date: Date pet was last seen
                - description: Additional description
                - photos: List of photo URLs
                - facebook_share_enabled: Whether to share on Facebook
                
        Returns:
            Lost pet report data
        """
        response = self._make_request('POST', '/api/v2/lost_pet_finder/report', data=pet_data)
        return response.get('data', {})
    
    def search_lost_pets(self, search_criteria: Dict[str, Any]) -> Dict[str, Any]:
        """
        Search for lost pets
        
        Args:
            search_criteria: Dictionary containing search parameters
                - breed: Pet breed to search for
                - color: Pet color to search for
                - age_range: Dictionary with min and max age
                - radius_km: Search radius in kilometers
                - latitude: Search center latitude
                - longitude: Search center longitude
                
        Returns:
            Search results with lost pets
        """
        response = self._make_request('GET', '/api/v2/lost_pet_finder/search', params=search_criteria)
        return response.get('data', {})
    
    def report_pet_sighting(self, sighting_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Report a pet sighting
        
        Args:
            sighting_data: Dictionary containing sighting information
                - lost_pet_report_id: ID of the lost pet report
                - location: Where the pet was seen
                - sighting_date: Date of the sighting
                - description: Description of what was seen
                - confidence_level: Confidence in the sighting (low/medium/high)
                
        Returns:
            Sighting report data
        """
        response = self._make_request('POST', '/api/v2/lost_pet_finder/sighting', data=sighting_data)
        return response.get('data', {})
    
    def mark_pet_found(self, report_id: int, found_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Mark a lost pet as found
        
        Args:
            report_id: ID of the lost pet report
            found_data: Dictionary containing found information
                - found_location: Where the pet was found
                - found_details: Additional details about the finding
                
        Returns:
            Updated report data
        """
        response = self._make_request('PUT', '/api/v2/lost_pet_finder/found', data=found_data)
        return response.get('data', {})
    
    def get_lost_pet_statistics(self) -> Dict[str, Any]:
        """
        Get lost pet system statistics
        
        Returns:
            Statistics data including total reports, success rates, etc.
        """
        response = self._make_request('GET', '/api/v2/lost_pet_finder/statistics')
        return response.get('data', {})
    
    # Blockchain & NFT Management
    def mint_cat_nft(self, cat_id: int, network: str = 'ethereum', metadata: Dict[str, Any] = None) -> Dict[str, Any]:
        """
        Mint an NFT for a cat
        
        Args:
            cat_id: ID of the cat
            network: Blockchain network (ethereum, polygon, bsc, solana)
            metadata: Additional NFT metadata
            
        Returns:
            NFT minting data
        """
        data = {
            'cat_id': cat_id,
            'network': network,
            'metadata': metadata or {}
        }
        
        response = self._make_request('POST', '/api/v2/advanced_features/blockchain?action=mint-nft', data=data)
        return response.get('data', {})
    
    def transfer_nft(self, nft_id: int, to_user_id: int, network: str = 'ethereum') -> Dict[str, Any]:
        """
        Transfer NFT ownership
        
        Args:
            nft_id: ID of the NFT to transfer
            to_user_id: ID of the user to transfer to
            network: Blockchain network
            
        Returns:
            Transfer transaction data
        """
        data = {
            'nft_id': nft_id,
            'to_user_id': to_user_id,
            'network': network
        }
        
        response = self._make_request('POST', '/api/v2/advanced_features/blockchain?action=transfer-nft', data=data)
        return response.get('data', {})
    
    def verify_nft_ownership(self, nft_id: int) -> Dict[str, Any]:
        """
        Verify NFT ownership
        
        Args:
            nft_id: ID of the NFT to verify
            
        Returns:
            Ownership verification data
        """
        response = self._make_request('GET', f'/api/v2/advanced_features/blockchain?action=verify-nft&nft_id={nft_id}')
        return response.get('data', {})
    
    def get_nft_collection(self, network: str = None) -> Dict[str, Any]:
        """
        Get user's NFT collection
        
        Args:
            network: Optional blockchain network filter
            
        Returns:
            NFT collection data
        """
        params = {'action': 'collection'}
        if network:
            params['network'] = network
            
        response = self._make_request('GET', '/api/v2/advanced_features/blockchain', params=params)
        return response.get('data', {})
    
    def get_blockchain_statistics(self) -> Dict[str, Any]:
        """
        Get blockchain system statistics
        
        Returns:
            Blockchain statistics data
        """
        response = self._make_request('GET', '/api/v2/advanced_features/blockchain?action=stats')
        return response.get('data', {})
    
    # Machine Learning Personality Prediction
    def predict_cat_personality(self, cat_id: int, include_confidence: bool = True) -> Dict[str, Any]:
        """
        Predict cat personality using ML
        
        Args:
            cat_id: ID of the cat
            include_confidence: Whether to include confidence scores
            
        Returns:
            Personality prediction data
        """
        params = {'action': 'predict', 'cat_id': cat_id, 'confidence': include_confidence}
        response = self._make_request('GET', '/api/v2/advanced_features/ml-personality', params=params)
        return response.get('data', {})
    
    def get_personality_insights(self, cat_id: int) -> Dict[str, Any]:
        """
        Get detailed personality insights for a cat
        
        Args:
            cat_id: ID of the cat
            
        Returns:
            Personality insights data
        """
        params = {'action': 'insights', 'cat_id': cat_id}
        response = self._make_request('GET', '/api/v2/advanced_features/ml-personality', params=params)
        return response.get('data', {})
    
    def record_behavior_observation(self, cat_id: int, behavior_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Record a behavior observation for ML training
        
        Args:
            cat_id: ID of the cat
            behavior_data: Dictionary containing behavior information
                - type: Behavior type (play, social, explore, etc.)
                - intensity: Intensity level (1-10)
                - duration: Duration in seconds
                - context: Environmental context
                
        Returns:
            Observation recording data
        """
        data = {
            'action': 'observe',
            'cat_id': cat_id,
            **behavior_data
        }
        
        response = self._make_request('POST', '/api/v2/advanced_features/ml-personality', data=data)
        return response.get('data', {})
    
    def update_genetic_data(self, cat_id: int, genetic_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Update cat genetic data for ML analysis
        
        Args:
            cat_id: ID of the cat
            genetic_data: Dictionary containing genetic information
                - heritage_score: Heritage score (0-100)
                - coat_pattern: Coat pattern information
                - markers: Genetic markers
                
        Returns:
            Genetic data update confirmation
        """
        data = {
            'action': 'genetic',
            'cat_id': cat_id,
            **genetic_data
        }
        
        response = self._make_request('POST', '/api/v2/advanced_features/ml-personality', data=data)
        return response.get('data', {})
    
    def get_ml_training_status(self) -> Dict[str, Any]:
        """
        Get ML model training status
        
        Returns:
            Training status and metrics
        """
        response = self._make_request('GET', '/api/v2/advanced_features/ml-personality?action=training')
        return response.get('data', {})
    
    # Metaverse & VR Worlds
    def create_metaverse_world(self, world_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Create a new metaverse world
        
        Args:
            world_data: Dictionary containing world information
                - name: World name
                - type: World type (cat_park, virtual_home, adventure_zone, etc.)
                - max_players: Maximum number of players
                - access_level: Access level (public, friends, private)
                
        Returns:
            Created world data
        """
        data = {
            'action': 'create-world',
            **world_data
        }
        
        response = self._make_request('POST', '/api/v2/advanced_features/metaverse', data=data)
        return response.get('data', {})
    
    def join_metaverse_world(self, world_id: int, cat_id: int = None) -> Dict[str, Any]:
        """
        Join a metaverse world
        
        Args:
            world_id: ID of the world to join
            cat_id: Optional cat ID to use in the world
            
        Returns:
            World joining data
        """
        data = {
            'action': 'join-world',
            'world_id': world_id
        }
        if cat_id:
            data['cat_id'] = cat_id
            
        response = self._make_request('POST', '/api/v2/advanced_features/metaverse', data=data)
        return response.get('data', {})
    
    def leave_metaverse_world(self, world_id: int) -> Dict[str, Any]:
        """
        Leave a metaverse world
        
        Args:
            world_id: ID of the world to leave
            
        Returns:
            World leaving confirmation
        """
        data = {'action': 'leave-world', 'world_id': world_id}
        response = self._make_request('POST', '/api/v2/advanced_features/metaverse', data=data)
        return response.get('data', {})
    
    def list_metaverse_worlds(self, filters: Dict[str, Any] = None) -> Dict[str, Any]:
        """
        List available metaverse worlds
        
        Args:
            filters: Optional filters for world listing
            
        Returns:
            List of available worlds
        """
        params = {'action': 'worlds'}
        if filters:
            params.update(filters)
            
        response = self._make_request('GET', '/api/v2/advanced_features/metaverse', params=params)
        return response.get('data', {})
    
    def perform_vr_interaction(self, world_id: int, interaction_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Perform a VR interaction in a metaverse world
        
        Args:
            world_id: ID of the world
            interaction_data: Dictionary containing interaction information
                - type: Interaction type
                - target_data: Target data for the interaction
                
        Returns:
            Interaction results
        """
        data = {
            'action': 'interact',
            'world_id': world_id,
            **interaction_data
        }
        
        response = self._make_request('POST', '/api/v2/advanced_features/metaverse', data=data)
        return response.get('data', {})
    
    def get_metaverse_statistics(self) -> Dict[str, Any]:
        """
        Get metaverse system statistics
        
        Returns:
            Metaverse statistics data
        """
        response = self._make_request('GET', '/api/v2/advanced_features/metaverse?action=stats')
        return response.get('data', {})
    
    # Webhook System
    def create_webhook(self, webhook_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Create a new webhook subscription
        
        Args:
            webhook_data: Dictionary containing webhook information
                - url: Webhook endpoint URL
                - events: List of events to subscribe to
                - secret: Optional webhook secret
                - headers: Optional custom headers
                
        Returns:
            Created webhook data
        """
        data = {
            'action': 'create',
            **webhook_data
        }
        
        response = self._make_request('POST', '/api/v2/advanced_features/webhooks', data=data)
        return response.get('data', {})
    
    def list_webhooks(self) -> Dict[str, Any]:
        """
        List user's webhook subscriptions
        
        Returns:
            List of webhook subscriptions
        """
        response = self._make_request('GET', '/api/v2/advanced_features/webhooks?action=list')
        return response.get('data', {})
    
    def update_webhook(self, webhook_id: int, updates: Dict[str, Any]) -> Dict[str, Any]:
        """
        Update a webhook subscription
        
        Args:
            webhook_id: ID of the webhook to update
            updates: Dictionary containing updates
            
        Returns:
            Updated webhook data
        """
        data = {
            'action': 'update',
            'webhook_id': webhook_id,
            **updates
        }
        
        response = self._make_request('POST', '/api/v2/advanced_features/webhooks', data=data)
        return response.get('data', {})
    
    def delete_webhook(self, webhook_id: int) -> bool:
        """
        Delete a webhook subscription
        
        Args:
            webhook_id: ID of the webhook to delete
            
        Returns:
            True if successful
        """
        data = {'action': 'delete', 'webhook_id': webhook_id}
        self._make_request('POST', '/api/v2/advanced_features/webhooks', data=data)
        return True
    
    def test_webhook(self, webhook_id: int) -> Dict[str, Any]:
        """
        Test a webhook subscription
        
        Args:
            webhook_id: ID of the webhook to test
            
        Returns:
            Test results
        """
        data = {'action': 'test', 'webhook_id': webhook_id}
        response = self._make_request('POST', '/api/v2/advanced_features/webhooks', data=data)
        return response.get('data', {})
    
    def get_webhook_logs(self, webhook_id: int, limit: int = 100) -> Dict[str, Any]:
        """
        Get webhook delivery logs
        
        Args:
            webhook_id: ID of the webhook
            limit: Maximum number of logs to return
            
        Returns:
            Webhook delivery logs
        """
        params = {
            'action': 'logs',
            'webhook_id': webhook_id,
            'limit': limit
        }
        
        response = self._make_request('GET', '/api/v2/advanced_features/webhooks', params=params)
        return response.get('data', {})
    
    # Analytics Dashboard
    def get_analytics_data(self, analytics_type: str = 'overview', filters: Dict[str, Any] = None) -> Dict[str, Any]:
        """
        Get analytics data from the dashboard
        
        Args:
            analytics_type: Type of analytics (overview, user_behavior, cat_interactions, etc.)
            filters: Optional filters for the analytics
            
        Returns:
            Analytics data
        """
        params = {'type': analytics_type}
        if filters:
            params.update(filters)
            
        response = self._make_request('GET', '/web/analytics_dashboard.php', params=params)
        return response.get('data', {})
    
    # Health Check
    def health_check(self) -> Dict[str, Any]:
        """
        Check API health status
        
        Returns:
            Health status information
        """
        response = self._make_request('GET', '/api/health.php')
        return response
    
    # Utility Methods
    def get_api_info(self) -> Dict[str, Any]:
        """
        Get API information and version
        
        Returns:
            API information
        """
        response = self._make_request('GET', '/api/')
        return response
    
    def get_rate_limit_info(self) -> Dict[str, Any]:
        """
        Get current rate limit information
        
        Returns:
            Rate limit information
        """
        # This would typically be available in response headers
        # For now, we'll make a lightweight request to check
        response = self._make_request('GET', '/api/health.php')
        return {
            'remaining': response.get('rate_limit_remaining', 'unknown'),
            'reset_time': response.get('rate_limit_reset', 'unknown')
        }
