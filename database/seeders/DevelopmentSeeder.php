<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DevelopmentActivity;
use App\Models\DevelopmentTip;

class DevelopmentSeeder extends Seeder
{
    public function run()
    {
        // Physical Activities
        DevelopmentActivity::create([
            'title' => 'Tummy Time',
            'description' => 'Place baby on their tummy while awake to strengthen neck and shoulder muscles.',
            'category' => 'physical',
            'min_age_months' => 0,
            'max_age_months' => 6,
            'benefits' => [
                'Strengthens neck and shoulder muscles',
                'Prevents flat spots on the head',
                'Improves motor skills'
            ],
            'instructions' => [
                'Place baby on a flat, firm surface',
                'Start with 3-5 minutes, several times a day',
                'Use toys to encourage head lifting'
            ]
        ]);

        // Cognitive Activities
        DevelopmentActivity::create([
            'title' => 'Object Tracking',
            'description' => 'Move colorful objects slowly in front of baby\'s face to encourage visual tracking.',
            'category' => 'cognitive',
            'min_age_months' => 1,
            'max_age_months' => 4,
            'benefits' => [
                'Develops visual tracking skills',
                'Enhances concentration',
                'Stimulates brain development'
            ],
            'instructions' => [
                'Hold a colorful toy about 8-12 inches from baby\'s face',
                'Move it slowly from side to side',
                'Watch if baby\'s eyes follow the object'
            ]
        ]);

        // Social Activities
        DevelopmentActivity::create([
            'title' => 'Mirror Play',
            'description' => 'Show baby their reflection in a mirror and interact with them.',
            'category' => 'social',
            'min_age_months' => 3,
            'max_age_months' => 12,
            'benefits' => [
                'Develops self-recognition',
                'Encourages social interaction',
                'Promotes emotional development'
            ],
            'instructions' => [
                'Use an unbreakable mirror',
                'Point to baby\'s reflection and name body parts',
                'Make faces and encourage imitation'
            ]
        ]);

        // Language Activities
        DevelopmentActivity::create([
            'title' => 'Reading Time',
            'description' => 'Read simple board books with baby, pointing to pictures and naming objects.',
            'category' => 'language',
            'min_age_months' => 4,
            'max_age_months' => 12,
            'benefits' => [
                'Builds vocabulary',
                'Develops listening skills',
                'Strengthens parent-child bond'
            ],
            'instructions' => [
                'Choose sturdy board books with simple pictures',
                'Point to and name objects in the book',
                'Make reading a daily routine'
            ]
        ]);

        // Development Tips
        DevelopmentTip::create([
            'title' => 'Sleep Position Safety',
            'content' => 'Always place your baby on their back to sleep. This position has been proven to reduce the risk of SIDS.',
            'category' => 'physical',
            'min_age_months' => 0,
            'max_age_months' => 12,
            'source' => 'American Academy of Pediatrics',
            'additional_resources' => [
                'https://www.aap.org/safe-sleep',
                'Safe Sleep Guidelines PDF'
            ]
        ]);

        DevelopmentTip::create([
            'title' => 'Responsive Parenting',
            'content' => 'Respond consistently to your baby\'s cues. This helps build trust and secure attachment.',
            'category' => 'social',
            'min_age_months' => 0,
            'max_age_months' => 12,
            'source' => 'Child Development Institute',
            'additional_resources' => [
                'Understanding Baby Cues Guide',
                'Building Secure Attachment Tips'
            ]
        ]);

        DevelopmentTip::create([
            'title' => 'Brain Development Through Play',
            'content' => 'Simple games like peek-a-boo help develop object permanence and cognitive skills.',
            'category' => 'cognitive',
            'min_age_months' => 4,
            'max_age_months' => 12,
            'source' => 'Zero to Three',
            'additional_resources' => [
                'Brain Development Research',
                'Age-Appropriate Games Guide'
            ]
        ]);

        DevelopmentTip::create([
            'title' => 'Language Development',
            'content' => 'Talk to your baby throughout the day. Narrate your activities to expose them to language.',
            'category' => 'language',
            'min_age_months' => 0,
            'max_age_months' => 12,
            'source' => 'Speech Language Association',
            'additional_resources' => [
                'Early Language Milestones',
                'Communication Activities'
            ]
        ]);
    }
} 