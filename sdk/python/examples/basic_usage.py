#!/usr/bin/env python3
"""
🐱 Purrr.love Python SDK - Basic Usage Example
Simple example demonstrating basic SDK functionality
"""

import os
import sys
from datetime import datetime

# Add the parent directory to the path so we can import the SDK
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))

from purrr_love_sdk import PurrrLoveClient, Cat, PersonalityType, MoodState
from purrr_love_sdk.exceptions import PurrrLoveError, AuthenticationError, NotFoundError


def main():
    """Main example function"""
    print("🐱 Purrr.love Python SDK - Basic Usage Example")
    print("=" * 50)
    
    # Configuration
    api_key = os.getenv("PURRR_LOVE_API_KEY")
    base_url = os.getenv("PURRR_LOVE_BASE_URL", "https://api.purrr.love")
    
    if not api_key:
        print("❌ Please set PURRR_LOVE_API_KEY environment variable")
        print("   export PURRR_LOVE_API_KEY='your_api_key_here'")
        return
    
    try:
        # Initialize client
        print(f"🔌 Connecting to {base_url}...")
        client = PurrrLoveClient(base_url=base_url, api_key=api_key)
        print("✅ Connected successfully!")
        
        # Get user's cats
        print("\n🐱 Fetching your cats...")
        cats = client.get_cats()
        
        if not cats:
            print("📝 No cats found. Let's create one!")
            cat = create_sample_cat(client)
        else:
            print(f"🎉 Found {len(cats)} cat(s)!")
            cat = cats[0]  # Use the first cat for examples
        
        # Display cat information
        display_cat_info(cat)
        
        # Play with the cat
        print("\n🎮 Playing with the cat...")
        play_result = client.play_with_cat(
            cat_id=cat.id,
            game_type="laser_pointer",
            duration=10
        )
        print(f"✅ Play session completed! Result: {play_result}")
        
        # Train the cat
        print("\n🎓 Training the cat...")
        train_result = client.train_cat(
            cat_id=cat.id,
            command="sit",
            difficulty="easy"
        )
        print(f"✅ Training completed! Result: {train_result}")
        
        # Care for the cat
        print("\n💝 Caring for the cat...")
        care_result = client.care_for_cat(
            cat_id=cat.id,
            care_type="feeding",
            food_type="premium_cat_food",
            amount="1_cup"
        )
        print(f"✅ Care completed! Result: {care_result}")
        
        # Get AI insights
        print("\n🤖 Getting AI insights...")
        try:
            ai_insights = client.get_ai_insights(cat_id=cat.id)
            print(f"🧠 AI Learning Progress: {ai_insights.get('learning_progress', 'N/A')}")
            print(f"📊 Behavior Patterns: {len(ai_insights.get('behavior_patterns', []))} patterns")
        except Exception as e:
            print(f"⚠️  AI insights not available: {e}")
        
        # Browse trading offers
        print("\n🔄 Browsing trading offers...")
        try:
            offers = client.get_trading_offers(limit=5)
            print(f"📋 Found {len(offers)} trading offers")
            for offer in offers[:3]:  # Show first 3
                print(f"   💰 ${offer.price} - {offer.description[:50]}...")
        except Exception as e:
            print(f"⚠️  Trading offers not available: {e}")
        
        # Find cat shows
        print("\n👑 Looking for cat shows...")
        try:
            shows = client.get_cat_shows(filters={'status': 'upcoming'}, limit=5)
            print(f"🎪 Found {len(shows)} upcoming shows")
            for show in shows[:3]:  # Show first 3
                print(f"   🏆 {show.name} - {show.start_date.strftime('%Y-%m-%d')}")
        except Exception as e:
            print(f"⚠️  Cat shows not available: {e}")
        
        # Get user statistics
        print("\n📊 Getting user statistics...")
        try:
            user_stats = client.get_user_stats()
            print(f"👤 Total cats: {user_stats.get('total_cats', 'N/A')}")
            print(f"🎮 Games played: {user_stats.get('games_played', 'N/A')}")
            print(f"🏆 Achievements: {user_stats.get('achievements', 'N/A')}")
        except Exception as e:
            print(f"⚠️  User stats not available: {e}")
        
        print("\n🎉 Example completed successfully!")
        print("🐾 Explore more features in the documentation!")
        
    except AuthenticationError as e:
        print(f"❌ Authentication failed: {e}")
        print("   Please check your API key")
    except NotFoundError as e:
        print(f"❌ Resource not found: {e}")
    except PurrrLoveError as e:
        print(f"❌ API error: {e}")
    except Exception as e:
        print(f"❌ Unexpected error: {e}")


def create_sample_cat(client):
    """Create a sample cat for demonstration"""
    print("🐱 Creating a sample cat...")
    
    cat = client.create_cat(
        name="Whiskers",
        species="domestic_shorthair",
        personality_type="playful",
        breed="mixed"
    )
    
    print(f"✅ Created cat: {cat.name} (ID: {cat.id})")
    return cat


def display_cat_info(cat):
    """Display detailed cat information"""
    print(f"\n🐱 Cat Information:")
    print(f"   Name: {cat.name}")
    print(f"   Breed: {cat.breed}")
    print(f"   Personality: {cat.personality_type.value}")
    print(f"   Mood: {cat.mood.value}")
    print(f"   Level: {cat.level}")
    print(f"   Experience: {cat.experience}")
    print(f"   Health: {cat.health}%")
    print(f"   Hunger: {cat.hunger}%")
    print(f"   Happiness: {cat.happiness}%")
    print(f"   Energy: {cat.energy}%")
    print(f"   Age: {cat.age_days} days")


if __name__ == "__main__":
    main()
