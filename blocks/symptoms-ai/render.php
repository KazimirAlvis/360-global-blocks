<?php
/**
 * Render callback for Symptoms AI block
 */
function global360blocks_render_symptoms_ai_block($attributes) {
    global360blocks_enqueue_block_assets_from_manifest(
        'global360blocks/symptoms-ai',
        array( 'style' => false )
    );

    $symptom = isset($attributes['symptom']) ? esc_html($attributes['symptom']) : '';
    $ai_content = isset($attributes['ai_content']) ? $attributes['ai_content'] : '';
    $show_disclaimer = isset($attributes['show_disclaimer']) ? $attributes['show_disclaimer'] : true;
    
    // Return empty if no content to display
    if (empty($ai_content) && empty($symptom)) {
        return '';
    }
    
    $wrapper_attributes = get_block_wrapper_attributes(array(
        'class' => 'wp-block-global360blocks-symptoms-ai'
    ));
    
    $wrapper_attributes = get_block_wrapper_attributes(array(
        'class' => 'wp-block-global360blocks-symptoms-ai'
    ));
    
    $output = '<div ' . $wrapper_attributes . '>';
    
    if (!empty($symptom) && !empty($ai_content)) {
        $output .= '<div class="symptoms-ai-content max_width_content">';
        $output .= '<div class="symptom-title">';
        $output .= '<h2>Information About: ' . $symptom . '</h2>';
        $output .= '</div>';
        
        $output .= '<div class="ai-generated-content">';
        $output .= wp_kses_post($ai_content);
        $output .= '</div>';
        
        if ($show_disclaimer) {
            $output .= '<div class="medical-disclaimer">';
            $output .= '<p><strong>⚠️ Medical Disclaimer:</strong> This information is for educational purposes only and should not replace professional medical advice. Always consult with a qualified healthcare provider for proper diagnosis and treatment.</p>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Generate symptoms content using templates (FREE version)
 */
function global360blocks_generate_symptoms_content($symptom) {
    // Medical content templates
    $templates = array(
        'knee pain' => array(
            'causes' => 'Common causes include sports injuries, arthritis, ligament tears, meniscus damage, overuse, or underlying inflammatory conditions.',
            'symptoms' => 'Symptoms may include pain, swelling, stiffness, instability, difficulty bearing weight, clicking sounds, or reduced range of motion.',
            'treatment' => 'Initial treatment often includes rest, ice application, compression, elevation (RICE), over-the-counter anti-inflammatory medications, and gentle exercises.',
            'when_to_see_doctor' => 'Seek medical attention for severe pain, significant swelling, inability to bear weight, visible deformity, or symptoms that persist beyond a few days.'
        ),
        'headache' => array(
            'causes' => 'Headaches can result from stress, dehydration, eye strain, poor posture, sinus problems, hormonal changes, or certain foods and medications.',
            'symptoms' => 'May present as throbbing, aching, or sharp pain in the head, often accompanied by sensitivity to light or sound, nausea, or neck tension.',
            'treatment' => 'Treatment includes adequate hydration, rest in a dark quiet room, stress management, regular sleep schedule, and over-the-counter pain relievers as directed.',
            'when_to_see_doctor' => 'Consult a healthcare provider for sudden severe headaches, headaches with fever, vision changes, confusion, or headaches that worsen over time.'
        ),
        'back pain' => array(
            'causes' => 'Lower back pain often stems from muscle strain, poor posture, herniated discs, arthritis, or structural problems with the spine.',
            'symptoms' => 'Symptoms include aching, stiffness, muscle spasms, pain that radiates to legs, or difficulty standing straight or walking.',
            'treatment' => 'Conservative treatment includes rest, heat/cold therapy, gentle stretching, maintaining good posture, and gradual return to normal activities.',
            'when_to_see_doctor' => 'Seek medical care for severe pain, pain following injury, numbness or weakness in legs, or pain that interferes with daily activities.'
        ),
        'chest pain' => array(
            'causes' => 'Chest pain can result from heart conditions, lung problems, muscle strain, acid reflux, anxiety, or costochondritis (chest wall inflammation).',
            'symptoms' => 'May present as sharp, dull, burning, or crushing pain in the chest, sometimes radiating to arms, neck, jaw, or back.',
            'treatment' => 'Treatment depends on the underlying cause. For non-cardiac causes, rest, stress management, and avoiding triggers may help.',
            'when_to_see_doctor' => 'Seek immediate medical attention for severe chest pain, especially with shortness of breath, sweating, nausea, or pain radiating to arms or jaw.'
        ),
        'fever' => array(
            'causes' => 'Fever typically indicates the body is fighting an infection, such as viral or bacterial illness, but can also result from inflammatory conditions.',
            'symptoms' => 'Elevated body temperature, chills, sweating, headache, muscle aches, weakness, and sometimes nausea or loss of appetite.',
            'treatment' => 'Management includes rest, increased fluid intake, fever-reducing medications like acetaminophen or ibuprofen, and staying cool.',
            'when_to_see_doctor' => 'Consult a healthcare provider for fever above 103°F, fever lasting more than 3 days, or fever accompanied by severe symptoms.'
        ),
        'sore throat' => array(
            'causes' => 'Sore throats are commonly caused by viral infections, bacterial infections (like strep throat), allergies, or dry air exposure.',
            'symptoms' => 'Pain, scratchiness, difficulty swallowing, swollen glands, red or white patches in throat, and sometimes fever.',
            'treatment' => 'Treatment includes warm salt water gargles, throat lozenges, warm liquids, humidified air, and pain relievers as needed.',
            'when_to_see_doctor' => 'See a healthcare provider for severe throat pain, difficulty swallowing, high fever, or symptoms lasting more than a week.'
        )
    );
    
    $symptom_lower = strtolower(trim($symptom));
    
    // Find exact match or partial match
    $template = null;
    if (isset($templates[$symptom_lower])) {
        $template = $templates[$symptom_lower];
    } else {
        // Try partial matching
        foreach ($templates as $key => $value) {
            if (strpos($symptom_lower, $key) !== false || strpos($key, $symptom_lower) !== false) {
                $template = $value;
                break;
            }
        }
    }
    
    // Fallback to generic template
    if (!$template) {
        $template = array(
            'causes' => 'Various factors can contribute to this condition, including lifestyle factors, underlying health conditions, injuries, or environmental influences.',
            'symptoms' => 'Symptoms may vary in severity and presentation depending on the individual and underlying cause of the condition.',
            'treatment' => 'Treatment approaches should be tailored to the individual and may include lifestyle modifications, home remedies, or medical interventions.',
            'when_to_see_doctor' => 'It is advisable to consult with a qualified healthcare provider for proper evaluation, diagnosis, and personalized treatment recommendations.'
        );
    }
    
    // Generate formatted content
    $content = '<h4>Common Causes</h4>';
    $content .= '<p>' . $template['causes'] . '</p>';
    $content .= '<h4>Typical Symptoms</h4>';
    $content .= '<p>' . $template['symptoms'] . '</p>';
    $content .= '<h4>Basic Care & Treatment</h4>';
    $content .= '<p>' . $template['treatment'] . '</p>';
    $content .= '<h4>When to See a Doctor</h4>';
    $content .= '<p>' . $template['when_to_see_doctor'] . '</p>';
    
    return $content;
}
