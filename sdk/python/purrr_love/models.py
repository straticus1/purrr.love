"""
🐱 Purrr.love Python SDK - Data Models
Data models for the Purrr.love API
"""

from typing import Dict, List, Optional, Any, Union
from datetime import datetime
from dataclasses import dataclass, field
from enum import Enum


class PersonalityType(Enum):
    """Cat personality types"""
    PLAYFUL = "playful"
    SHY = "shy"
    AGGRESSIVE = "aggressive"
    CALM = "calm"
    CURIOUS = "curious"
    INDEPENDENT = "independent"
    SOCIAL = "social"
    LAZY = "lazy"


class MoodState(Enum):
    """Cat mood states"""
    HAPPY = "happy"
    EXCITED = "excited"
    CALM = "calm"
    SLEEPY = "sleepy"
    PLAYFUL = "playful"
    HUNGRY = "hungry"
    IRRITATED = "irritated"
    SICK = "sick"


class CatBreed(Enum):
    """Common cat breeds"""
    PERSIAN = "persian"
    SIAMESE = "siamese"
    MAINE_COON = "maine_coon"
    RAGDOLL = "ragdoll"
    BENGAL = "bengal"
    BRITISH_SHORTHAIR = "british_shorthair"
    ABYSSINIAN = "abyssinian"
    RUSSIAN_BLUE = "russian_blue"
    MIXED = "mixed"


class GameType(Enum):
    """Cat game types"""
    MOUSE_HUNT = "mouse_hunt"
    YARN_CHASE = "yarn_chase"
    CAT_TOWER_CLIMBING = "cat_tower_climbing"
    BIRD_WATCHING = "bird_watching"
    LASER_POINTER = "laser_pointer"
    CAT_PUZZLE_BOX = "cat_puzzle_box"
    STRING_MAZE = "string_maze"
    BOX_FORT = "box_fort"
    CATNIP_FRENZY = "catnip_frenzy"
    LASER_TAG = "laser_tag"


class CareType(Enum):
    """Cat care types"""
    FEEDING = "feeding"
    GROOMING = "grooming"
    PLAYING = "playing"
    TRAINING = "training"
    VET_VISIT = "vet_visit"
    CLEANING = "cleaning"
    EXERCISE = "exercise"


@dataclass
class Cat:
    """Cat model"""
    id: int
    name: str
    species: str
    breed: str
    personality_type: PersonalityType
    mood: MoodState
    level: int = 1
    experience: int = 0
    health: int = 100
    hunger: int = 100
    happiness: int = 100
    energy: int = 100
    age_days: int = 0
    created_at: Optional[datetime] = None
    updated_at: Optional[datetime] = None
    
    # Advanced features
    ai_profile: Optional[Dict[str, Any]] = None
    vr_behavior: Optional[Dict[str, Any]] = None
    health_devices: List[Dict[str, Any]] = field(default_factory=list)
    trading_status: Optional[Dict[str, Any]] = None
    show_participation: List[Dict[str, Any]] = field(default_factory=list)
    multiplayer_status: Optional[Dict[str, Any]] = None
    
    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> 'Cat':
        """Create Cat instance from dictionary"""
        # Convert string enums to enum values
        if isinstance(data.get('personality_type'), str):
            data['personality_type'] = PersonalityType(data['personality_type'])
        if isinstance(data.get('mood'), str):
            data['mood'] = MoodState(data['mood'])
        
        # Convert timestamps to datetime objects
        if data.get('created_at'):
            data['created_at'] = datetime.fromisoformat(data['created_at'].replace('Z', '+00:00'))
        if data.get('updated_at'):
            data['updated_at'] = datetime.fromisoformat(data['updated_at'].replace('Z', '+00:00'))
        
        return cls(**data)
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert Cat instance to dictionary"""
        data = {
            'id': self.id,
            'name': self.name,
            'species': self.species,
            'breed': self.breed,
            'personality_type': self.personality_type.value,
            'mood': self.mood.value,
            'level': self.level,
            'experience': self.experience,
            'health': self.health,
            'hunger': self.hunger,
            'happiness': self.happiness,
            'energy': self.energy,
            'age_days': self.age_days,
        }
        
        if self.created_at:
            data['created_at'] = self.created_at.isoformat()
        if self.updated_at:
            data['updated_at'] = self.updated_at.isoformat()
        
        if self.ai_profile:
            data['ai_profile'] = self.ai_profile
        if self.vr_behavior:
            data['vr_behavior'] = self.vr_behavior
        if self.health_devices:
            data['health_devices'] = self.health_devices
        if self.trading_status:
            data['trading_status'] = self.trading_status
        if self.show_participation:
            data['show_participation'] = self.show_participation
        if self.multiplayer_status:
            data['multiplayer_status'] = self.multiplayer_status
        
        return data


@dataclass
class User:
    """User model"""
    id: int
    username: str
    email: str
    role: str = 'user'
    created_at: Optional[datetime] = None
    updated_at: Optional[datetime] = None
    
    # User metadata
    profile: Optional[Dict[str, Any]] = None
    preferences: Optional[Dict[str, Any]] = None
    statistics: Optional[Dict[str, Any]] = None
    
    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> 'User':
        """Create User instance from dictionary"""
        # Convert timestamps to datetime objects
        if data.get('created_at'):
            data['created_at'] = datetime.fromisoformat(data['created_at'].replace('Z', '+00:00'))
        if data.get('updated_at'):
            data['updated_at'] = datetime.fromisoformat(data['updated_at'].replace('Z', '+00:00'))
        
        return cls(**data)
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert User instance to dictionary"""
        data = {
            'id': self.id,
            'username': self.username,
            'email': self.email,
            'role': self.role,
        }
        
        if self.created_at:
            data['created_at'] = self.created_at.isoformat()
        if self.updated_at:
            data['updated_at'] = self.updated_at.isoformat()
        
        if self.profile:
            data['profile'] = self.profile
        if self.preferences:
            data['preferences'] = self.preferences
        if self.statistics:
            data['statistics'] = self.statistics
        
        return data


@dataclass
class ApiKey:
    """API Key model"""
    id: int
    name: str
    scopes: List[str]
    expires_at: Optional[datetime] = None
    ip_whitelist: Optional[List[str]] = None
    last_used_at: Optional[datetime] = None
    active: bool = True
    created_at: Optional[datetime] = None
    
    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> 'ApiKey':
        """Create ApiKey instance from dictionary"""
        # Convert timestamps to datetime objects
        if data.get('expires_at'):
            data['expires_at'] = datetime.fromisoformat(data['expires_at'].replace('Z', '+00:00'))
        if data.get('last_used_at'):
            data['last_used_at'] = datetime.fromisoformat(data['last_used_at'].replace('Z', '+00:00'))
        if data.get('created_at'):
            data['created_at'] = datetime.fromisoformat(data['created_at'].replace('Z', '+00:00'))
        
        return cls(**data)
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert ApiKey instance to dictionary"""
        data = {
            'id': self.id,
            'name': self.name,
            'scopes': self.scopes,
            'active': self.active,
        }
        
        if self.expires_at:
            data['expires_at'] = self.expires_at.isoformat()
        if self.last_used_at:
            data['last_used_at'] = self.last_used_at.isoformat()
        if self.created_at:
            data['created_at'] = self.created_at.isoformat()
        if self.ip_whitelist:
            data['ip_whitelist'] = self.ip_whitelist
        
        return data


@dataclass
class TradingOffer:
    """Trading Offer model"""
    id: int
    seller_id: int
    cat_id: int
    price: float
    currency: str = 'USD'
    description: str = ''
    status: str = 'pending'
    created_at: Optional[datetime] = None
    updated_at: Optional[datetime] = None
    
    # Additional offer details
    cat_details: Optional[Dict[str, Any]] = None
    seller_details: Optional[Dict[str, Any]] = None
    buyer_id: Optional[int] = None
    completed_at: Optional[datetime] = None
    
    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> 'TradingOffer':
        """Create TradingOffer instance from dictionary"""
        # Convert timestamps to datetime objects
        if data.get('created_at'):
            data['created_at'] = datetime.fromisoformat(data['created_at'].replace('Z', '+00:00'))
        if data.get('updated_at'):
            data['updated_at'] = datetime.fromisoformat(data['updated_at'].replace('Z', '+00:00'))
        if data.get('completed_at'):
            data['completed_at'] = datetime.fromisoformat(data['completed_at'].replace('Z', '+00:00'))
        
        return cls(**data)
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert TradingOffer instance to dictionary"""
        data = {
            'id': self.id,
            'seller_id': self.seller_id,
            'cat_id': self.cat_id,
            'price': self.price,
            'currency': self.currency,
            'description': self.description,
            'status': self.status,
        }
        
        if self.created_at:
            data['created_at'] = self.created_at.isoformat()
        if self.updated_at:
            data['updated_at'] = self.updated_at.isoformat()
        if self.completed_at:
            data['completed_at'] = self.completed_at.isoformat()
        
        if self.cat_details:
            data['cat_details'] = self.cat_details
        if self.seller_details:
            data['seller_details'] = self.seller_details
        if self.buyer_id:
            data['buyer_id'] = self.buyer_id
        
        return data


@dataclass
class CatShow:
    """Cat Show model"""
    id: int
    name: str
    organizer_id: int
    show_type: str
    categories: List[str]
    start_date: datetime
    end_date: datetime
    status: str = 'upcoming'
    max_participants: int = 100
    entry_fee: float = 0.0
    currency: str = 'USD'
    created_at: Optional[datetime] = None
    
    # Show details
    description: str = ''
    location: str = ''
    prizes: Optional[Dict[str, Any]] = None
    participants: List[Dict[str, Any]] = field(default_factory=list)
    results: Optional[Dict[str, Any]] = None
    
    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> 'CatShow':
        """Create CatShow instance from dictionary"""
        # Convert timestamps to datetime objects
        if data.get('start_date'):
            data['start_date'] = datetime.fromisoformat(data['start_date'].replace('Z', '+00:00'))
        if data.get('end_date'):
            data['end_date'] = datetime.fromisoformat(data['end_date'].replace('Z', '+00:00'))
        if data.get('created_at'):
            data['created_at'] = datetime.fromisoformat(data['created_at'].replace('Z', '+00:00'))
        
        return cls(**data)
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert CatShow instance to dictionary"""
        data = {
            'id': self.id,
            'name': self.name,
            'organizer_id': self.organizer_id,
            'show_type': self.show_type,
            'categories': self.categories,
            'start_date': self.start_date.isoformat(),
            'end_date': self.end_date.isoformat(),
            'status': self.status,
            'max_participants': self.max_participants,
            'entry_fee': self.entry_fee,
            'currency': self.currency,
            'description': self.description,
            'location': self.location,
        }
        
        if self.created_at:
            data['created_at'] = self.created_at.isoformat()
        if self.prizes:
            data['prizes'] = self.prizes
        if self.participants:
            data['participants'] = self.participants
        if self.results:
            data['results'] = self.results
        
        return data


@dataclass
class VRInteraction:
    """VR Interaction model"""
    session_id: str
    cat_id: int
    user_id: int
    interaction_type: str
    interaction_data: Dict[str, Any]
    timestamp: datetime
    response: Optional[Dict[str, Any]] = None
    
    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> 'VRInteraction':
        """Create VRInteraction instance from dictionary"""
        # Convert timestamp to datetime object
        if data.get('timestamp'):
            data['timestamp'] = datetime.fromisoformat(data['timestamp'].replace('Z', '+00:00'))
        
        return cls(**data)
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert VRInteraction instance to dictionary"""
        data = {
            'session_id': self.session_id,
            'cat_id': self.cat_id,
            'user_id': self.user_id,
            'interaction_type': self.interaction_type,
            'interaction_data': self.interaction_data,
            'timestamp': self.timestamp.isoformat(),
        }
        
        if self.response:
            data['response'] = self.response
        
        return data


@dataclass
class HealthDevice:
    """Health Device model"""
    id: int
    cat_id: int
    device_type: str
    device_name: str
    device_data: Dict[str, Any]
    last_reading: Optional[datetime] = None
    active: bool = True
    created_at: Optional[datetime] = None
    
    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> 'HealthDevice':
        """Create HealthDevice instance from dictionary"""
        # Convert timestamps to datetime objects
        if data.get('last_reading'):
            data['last_reading'] = datetime.fromisoformat(data['last_reading'].replace('Z', '+00:00'))
        if data.get('created_at'):
            data['created_at'] = datetime.fromisoformat(data['created_at'].replace('Z', '+00:00'))
        
        return cls(**data)
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert HealthDevice instance to dictionary"""
        data = {
            'id': self.id,
            'cat_id': self.cat_id,
            'device_type': self.device_type,
            'device_name': self.device_name,
            'device_data': self.device_data,
            'active': self.active,
        }
        
        if self.last_reading:
            data['last_reading'] = self.last_reading.isoformat()
        if self.created_at:
            data['created_at'] = self.created_at.isoformat()
        
        return data


@dataclass
class MultiplayerSession:
    """Multiplayer Session model"""
    session_id: str
    room_type: str
    participants: List[Dict[str, Any]]
    max_participants: int = 20
    status: str = 'active'
    created_at: Optional[datetime] = None
    
    # Session details
    room_settings: Optional[Dict[str, Any]] = None
    activities: List[str] = field(default_factory=list)
    
    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> 'MultiplayerSession':
        """Create MultiplayerSession instance from dictionary"""
        # Convert timestamp to datetime object
        if data.get('created_at'):
            data['created_at'] = datetime.fromisoformat(data['created_at'].replace('Z', '+00:00'))
        
        return cls(**data)
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert MultiplayerSession instance to dictionary"""
        data = {
            'session_id': self.session_id,
            'room_type': self.room_type,
            'participants': self.participants,
            'max_participants': self.max_participants,
            'status': self.status,
            'activities': self.activities,
        }
        
        if self.created_at:
            data['created_at'] = self.created_at.isoformat()
        if self.room_settings:
            data['room_settings'] = self.room_settings
        
        return data
