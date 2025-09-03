#!/usr/bin/env python3
"""
🐱 Purrr.love Python SDK - Advanced Features Example
Demonstrates advanced features like VR, AI learning, trading, shows, and multiplayer
"""

import os
import sys
import time
from datetime import datetime, timedelta

# Add the parent directory to the path so we can import the SDK
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))

from purrr_love_sdk import PurrrLoveClient, Cat, PersonalityType, MoodState
from purrr_love_sdk.exceptions import PurrrLoveError, AuthenticationError, NotFoundError


def main():
    """Main advanced features example function"""
    print("🐱 Purrr.love Python SDK - Advanced Features Example")
    print("=" * 60)
    
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
        
        # Get or create a cat for examples
        cats = client.get_cats()
        if not cats:
            print("📝 No cats found. Creating one for examples...")
            cat = create_sample_cat(client)
        else:
            cat = cats[0]
            print(f"🐱 Using existing cat: {cat.name}")
        
        # Run advanced feature examples
        run_vr_examples(client, cat)
        run_ai_learning_examples(client, cat)
        run_trading_examples(client, cat)
        run_show_examples(client, cat)
        run_multiplayer_examples(client, cat)
        run_health_monitoring_examples(client, cat)
        
        print("\n🎉 Advanced features example completed successfully!")
        print("🚀 You're now ready to build amazing cat gaming applications!")
        
    except Exception as e:
        print(f"❌ Error: {e}")


def run_vr_examples(client, cat):
    """Demonstrate VR interaction features"""
    print("\n🥽 VR Cat Interaction Examples")
    print("-" * 40)
    
    try:
        # Start VR session
        print("🎮 Starting VR session...")
        session = client.start_vr_session(
            cat_id=cat.id,
            vr_device="webvr"
        )
        session_id = session['session_id']
        print(f"✅ VR session started: {session_id}")
        
        # VR petting interaction
        print("🤲 VR petting interaction...")
        petting_response = client.vr_interact(
            session_id=session_id,
            interaction_type="petting",
            location="head",
            intensity="gentle",
            duration=5
        )
        print(f"🐱 Cat response: {petting_response.get('cat_reaction', 'N/A')}")
        
        # VR playing interaction
        print("🎯 VR playing interaction...")
        playing_response = client.vr_interact(
            session_id=session_id,
            interaction_type="playing",
            game_type="laser_pointer",
            difficulty="medium"
        )
        print(f"🎮 Play result: {playing_response.get('success_rate', 'N/A')}")
        
        # VR grooming interaction
        print("💇 VR grooming interaction...")
        grooming_response = client.vr_interact(
            session_id=session_id,
            interaction_type="grooming",
            tool="brush",
            pressure="light"
        )
        print(f"✨ Grooming effect: {grooming_response.get('happiness_increase', 'N/A')}")
        
    except Exception as e:
        print(f"⚠️  VR features not available: {e}")


def run_ai_learning_examples(client, cat):
    """Demonstrate AI learning features"""
    print("\n🤖 AI Cat Behavior Learning Examples")
    print("-" * 45)
    
    try:
        # Get AI insights
        print("🧠 Getting AI learning insights...")
        ai_insights = client.get_ai_insights(cat_id=cat.id)
        
        print(f"📊 Learning Progress: {ai_insights.get('learning_progress', 'N/A')}%")
        print(f"🧬 Behavior Patterns: {len(ai_insights.get('behavior_patterns', []))}")
        print(f"🎯 Learning Goals: {len(ai_insights.get('learning_goals', []))}")
        
        # Display behavior patterns
        patterns = ai_insights.get('behavior_patterns', [])
        if patterns:
            print("📈 Recent behavior patterns:")
            for pattern in patterns[:3]:
                print(f"   • {pattern.get('type', 'Unknown')}: {pattern.get('frequency', 'N/A')}")
        
        # Display learning recommendations
        recommendations = ai_insights.get('recommendations', [])
        if recommendations:
            print("💡 AI recommendations:")
            for rec in recommendations[:3]:
                print(f"   • {rec.get('title', 'Unknown')}: {rec.get('description', 'N/A')}")
        
    except Exception as e:
        print(f"⚠️  AI learning features not available: {e}")


def run_trading_examples(client, cat):
    """Demonstrate trading features"""
    print("\n🔄 Cross-Platform Cat Trading Examples")
    print("-" * 45)
    
    try:
        # Browse available offers
        print("📋 Browsing trading offers...")
        offers = client.get_trading_offers(limit=10)
        print(f"💰 Found {len(offers)} trading offers")
        
        if offers:
            print("📊 Sample offers:")
            for i, offer in enumerate(offers[:5], 1):
                print(f"   {i}. ${offer.price} - {offer.description[:60]}...")
                print(f"      Cat: {offer.cat_details.get('name', 'Unknown') if offer.cat_details else 'N/A'}")
                print(f"      Breed: {offer.cat_details.get('breed', 'Unknown') if offer.cat_details else 'N/A'}")
        
        # Create a sample trading offer (if user has permission)
        print("\n📝 Creating sample trading offer...")
        try:
            sample_offer = client.create_trading_offer(
                cat_id=cat.id,
                price=150.0,
                description=f"Beautiful {cat.breed} cat, well-trained and friendly",
                currency="USD"
            )
            print(f"✅ Created offer: ${sample_offer.price} - {sample_offer.description}")
            
            # Cancel the offer (cleanup)
            print("🗑️  Canceling sample offer...")
            # Note: This would require a cancel method in the API
            print("   (Offer cleanup would happen here)")
            
        except Exception as e:
            print(f"⚠️  Could not create trading offer: {e}")
        
    except Exception as e:
        print(f"⚠️  Trading features not available: {e}")


def run_show_examples(client, cat):
    """Demonstrate cat show features"""
    print("\n👑 Cat Show Competition Examples")
    print("-" * 40)
    
    try:
        # Find upcoming shows
        print("🎪 Finding upcoming cat shows...")
        shows = client.get_cat_shows(filters={'status': 'upcoming'}, limit=10)
        print(f"🏆 Found {len(shows)} upcoming shows")
        
        if shows:
            print("📅 Upcoming shows:")
            for i, show in enumerate(shows[:5], 1):
                print(f"   {i}. {show.name}")
                print(f"      Date: {show.start_date.strftime('%Y-%m-%d')}")
                print(f"      Categories: {', '.join(show.categories)}")
                print(f"      Entry Fee: ${show.entry_fee}")
                print(f"      Location: {show.location}")
        
        # Register for a show (if available)
        if shows:
            target_show = shows[0]
            print(f"\n📝 Registering for show: {target_show.name}")
            try:
                registration = client.register_cat_for_show(
                    cat_id=cat.id,
                    show_id=target_show.id,
                    categories=["beauty", "personality"]
                )
                print(f"✅ Registration successful! ID: {registration.get('registration_id', 'N/A')}")
            except Exception as e:
                print(f"⚠️  Could not register for show: {e}")
        
    except Exception as e:
        print(f"⚠️  Cat show features not available: {e}")


def run_multiplayer_examples(client, cat):
    """Demonstrate multiplayer features"""
    print("\n🌐 Real-time Multiplayer Examples")
    print("-" * 40)
    
    try:
        # Join multiplayer room
        print("🚪 Joining multiplayer room...")
        session = client.join_multiplayer_room(
            cat_id=cat.id,
            room_type="playground"
        )
        session_id = session['session_id']
        print(f"✅ Joined room: {session_id}")
        print(f"👥 Participants: {len(session.get('participants', []))}")
        
        # Perform multiplayer actions
        print("🎮 Performing multiplayer actions...")
        
        # Movement action
        move_action = client.multiplayer_action(
            session_id=session_id,
            action_type="move",
            direction="north",
            speed="walk"
        )
        print(f"🚶 Movement: {move_action.get('result', 'N/A')}")
        
        # Socialize action
        social_action = client.multiplayer_action(
            session_id=session_id,
            action_type="socialize",
            target_cat="nearest",
            interaction="greeting"
        )
        print(f"👋 Socialization: {social_action.get('result', 'N/A')}")
        
        # Play action
        play_action = client.multiplayer_action(
            session_id=session_id,
            action_type="play",
            game_type="chase",
            participants="all"
        )
        print(f"🎯 Multiplayer play: {play_action.get('result', 'N/A')}")
        
    except Exception as e:
        print(f"⚠️  Multiplayer features not available: {e}")


def run_health_monitoring_examples(client, cat):
    """Demonstrate health monitoring features"""
    print("\n🏥 Cat Health Monitoring Examples")
    print("-" * 40)
    
    try:
        # Register health device
        print("📱 Registering health monitoring device...")
        device_data = {
            "type": "smart_collar",
            "name": "WhiskerTracker Pro",
            "capabilities": ["activity", "heart_rate", "location", "temperature"],
            "manufacturer": "PurrrTech",
            "model": "WT-2024"
        }
        
        device = client.register_health_device(
            cat_id=cat.id,
            device_data=device_data
        )
        print(f"✅ Device registered: {device.get('device_name', 'N/A')}")
        
        # Get health summary
        print("📊 Getting health summary...")
        health_summary = client.get_health_summary(
            cat_id=cat.id,
            timeframe="7d"
        )
        
        print(f"🏥 Overall Health Score: {health_summary.get('overall_score', 'N/A')}%")
        print(f"💓 Heart Rate: {health_summary.get('heart_rate', 'N/A')} BPM")
        print(f"🌡️  Temperature: {health_summary.get('temperature', 'N/A')}°F")
        print(f"📈 Activity Level: {health_summary.get('activity_level', 'N/A')}")
        
        # Display health trends
        trends = health_summary.get('trends', {})
        if trends:
            print("📈 Health trends:")
            for metric, trend in list(trends.items())[:3]:
                print(f"   • {metric}: {trend.get('direction', 'N/A')} ({trend.get('change', 'N/A')})")
        
        # Display health alerts
        alerts = health_summary.get('alerts', [])
        if alerts:
            print("🚨 Health alerts:")
            for alert in alerts[:3]:
                print(f"   • {alert.get('type', 'Unknown')}: {alert.get('message', 'N/A')}")
        
    except Exception as e:
        print(f"⚠️  Health monitoring features not available: {e}")


def create_sample_cat(client):
    """Create a sample cat for examples"""
    print("🐱 Creating a sample cat for examples...")
    
    cat = client.create_cat(
        name="Whiskers",
        species="domestic_shorthair",
        personality_type="playful",
        breed="mixed"
    )
    
    print(f"✅ Created cat: {cat.name} (ID: {cat.id})")
    return cat


if __name__ == "__main__":
    main()
