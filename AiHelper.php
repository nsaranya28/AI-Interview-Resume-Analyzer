<?php
// AiHelper.php
// AI Integration layer (Gemini 1.5 Flash API + High Fidelity Fallback Simulation)

require_once __DIR__ . '/config.php';

/**
 * Direct request to Gemini API
 */
function callGeminiAPI($prompt, $jsonMode = false) {
    $apiKey = $GLOBALS['gemini_api_key'] ?? '';
    if (empty($apiKey)) {
        return null;
    }
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($apiKey);
    
    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];
    
    if ($jsonMode) {
        $payload["generationConfig"] = [
            "responseMimeType" => "application/json"
        ];
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Help with local XAMPP SSL issues
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        return null;
    }
    
    $data = json_decode($response, true);
    return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
}

/**
 * Analyzes resume and returns ATS Score, skills, suggestions, and grammar checks
 */
function analyzeResume($resumeText, $targetRole) {
    if (empty($targetRole)) {
        $targetRole = "General Professional";
    }
    
    $isDemo = $GLOBALS['demo_mode'];
    
    if (!$isDemo) {
        $prompt = "You are an advanced Applicant Tracking System (ATS) and HR specialist. "
                . "Analyze the following candidate resume for the target job role: '{$targetRole}'. "
                . "Compare the skills in the resume against typical industry demands for this role.\n\n"
                . "You MUST output a JSON response. The JSON schema MUST be exactly:\n"
                . "{\n"
                . "  \"ats_score\": <integer between 0 and 100>,\n"
                . "  \"matched_skills\": [<array of strings containing skills present in the resume relevant to the role>],\n"
                . "  \"missing_skills\": [<array of strings of key skills expected for a '{$targetRole}' but missing or weak in the resume>],\n"
                . "  \"grammar_issues\": [<array of strings detailing grammar, phrasing, typo issues, or list empty if none>],\n"
                . "  \"suggestions\": [<array of strings containing actionable feedback to improve the resume for this role>]\n"
                . "}\n\n"
                . "Resume Text:\n{$resumeText}";
                
        $result = callGeminiAPI($prompt, true);
        if ($result) {
            $parsed = json_decode(trim($result), true);
            if ($parsed && isset($parsed['ats_score'])) {
                return $parsed;
            }
        }
    }
    
    // Simulation fallback / Demo Mode
    return getMockAnalysis($resumeText, $targetRole);
}

/**
 * Generates 5 interview questions based on resume text and target role
 */
function generateInterviewQuestions($resumeText, $targetRole) {
    if (empty($targetRole)) {
        $targetRole = "General Professional";
    }
    
    $isDemo = $GLOBALS['demo_mode'];
    
    if (!$isDemo) {
        $prompt = "You are a professional hiring manager conducting a technical and behavioral mock interview for the role: '{$targetRole}'. "
                . "Review the candidate's resume details and generate exactly 5 relevant interview questions. "
                . "Make at least 3 questions technical/role-specific, and 2 behavioral/project-based based on their experience.\n\n"
                . "You MUST output your response in JSON format. The response must be a simple JSON array of 5 strings, for example:\n"
                . "[\"Question 1?\", \"Question 2?\", \"Question 3?\", \"Question 4?\", \"Question 5?\"]\n\n"
                . "Resume Details:\n{$resumeText}";
                
        $result = callGeminiAPI($prompt, true);
        if ($result) {
            $parsed = json_decode(trim($result), true);
            if (is_array($parsed) && count($parsed) >= 3) {
                return array_slice($parsed, 0, 5);
            }
        }
    }
    
    return getMockQuestions($resumeText, $targetRole);
}

/**
 * Evaluates candidate's answer to a mock interview question
 */
function evaluateInterviewAnswer($question, $userAnswer) {
    $isDemo = $GLOBALS['demo_mode'];
    
    if (!$isDemo) {
        $prompt = "You are an expert interviewer. Grade the candidate's answer to this interview question.\n"
                . "Question: \"{$question}\"\n"
                . "Candidate's Answer: \"{$userAnswer}\"\n\n"
                . "Evaluate the response for relevance, completeness, technical accuracy (if applicable), and communication skills. "
                . "You MUST respond in JSON format. The JSON schema MUST be exactly:\n"
                . "{\n"
                . "  \"score\": <integer between 1 and 10>,\n"
                . "  \"feedback\": \"<constructive feedback string pointing out what was good, what was missing, and how to improve>\"\n"
                . "}\n";
                
        $result = callGeminiAPI($prompt, true);
        if ($result) {
            $parsed = json_decode(trim($result), true);
            if ($parsed && isset($parsed['score'])) {
                return $parsed;
            }
        }
    }
    
    return getMockEvaluation($question, $userAnswer);
}

/* ========================================================================= */
/*                   HIGH-FIDELITY SIMULATION CODE FOR DEMO MODE              */
/* ========================================================================= */

function getMockAnalysis($text, $role) {
    $textLower = strtolower($text);
    $roleLower = strtolower($role);
    
    // Skill dictionaries by job family
    $skillMap = [
        'web' => ['HTML5', 'CSS3', 'JavaScript', 'React', 'Vue', 'Git', 'REST APIs', 'Node.js', 'PHP', 'Webpack', 'TailwindCSS'],
        'software' => ['Java', 'C++', 'Python', 'Git', 'Data Structures', 'Algorithms', 'SQL', 'Docker', 'Design Patterns', 'Unit Testing'],
        'data' => ['Python', 'SQL', 'R', 'Tableau', 'Power BI', 'Pandas', 'NumPy', 'Excel', 'Machine Learning', 'Data Cleaning', 'Statistics'],
        'manager' => ['Agile', 'Scrum', 'Project Management', 'Jira', 'Leadership', 'Stakeholder Management', 'Risk Analysis', 'Budgeting'],
        'design' => ['Figma', 'UI Design', 'UX Research', 'Adobe XD', 'Photoshop', 'Wireframing', 'Prototyping', 'User Flows']
    ];
    
    // Identify best matching category
    $category = 'web'; // default
    foreach ($skillMap as $key => $skills) {
        if (strpos($roleLower, $key) !== false) {
            $category = $key;
            break;
        }
    }
    
    $targetSkills = $skillMap[$category];
    $matched = [];
    $missing = [];
    
    // Simple parsing to detect matching skills
    foreach ($targetSkills as $skill) {
        if (strpos($textLower, strtolower($skill)) !== false) {
            $matched[] = $skill;
        } else {
            $missing[] = $skill;
        }
    }
    
    // Ensure we have at least some matched/missing for realism
    if (empty($matched)) {
        // Seed default matched skills if resume is too generic
        $matched = array_slice($targetSkills, 0, 3);
        $missing = array_slice($targetSkills, 3);
    }
    
    // Calculate a realistic score
    $matchCount = count($matched);
    $totalCount = count($targetSkills);
    $percentage = $totalCount > 0 ? ($matchCount / $totalCount) : 0.5;
    
    // Base score between 55 and 92
    $atsScore = intval(55 + ($percentage * 37));
    if ($atsScore > 98) $atsScore = 98;
    
    // Generate realistic suggestions based on results
    $suggestions = [
        "Incorporate more quantitative results (e.g., 'Improved database query speed by 25%').",
        "Add a dedicated 'Core Competencies' section near the top of the resume.",
        "Tailor your profile summary to explicitly target the '{$role}' title."
    ];
    
    foreach ($missing as $miss) {
        $suggestions[] = "Integrate keyword exposure for '{$miss}' by describing projects where you applied it.";
    }
    $suggestions = array_slice($suggestions, 0, 5); // Max 5 suggestions
    
    // Typos / Grammar check simulation
    $grammar = [];
    if (strpos($textLower, 'responsable') !== false || strpos($textLower, 'recieve') !== false) {
        $grammar[] = "Correct minor spelling errors (e.g. checked typos like 'responsable' or 'recieve').";
    }
    $grammar[] = "Ensure all bullet points start with strong action verbs (e.g. 'Led', 'Optimized', 'Architected').";
    $grammar[] = "Standardize date formatting across all previous employment blocks.";
    
    return [
        "ats_score" => $atsScore,
        "matched_skills" => $matched,
        "missing_skills" => $missing,
        "grammar_issues" => $grammar,
        "suggestions" => $suggestions
    ];
}

function getMockQuestions($text, $role) {
    $roleLower = strtolower($role);
    
    if (strpos($roleLower, 'developer') !== false || strpos($roleLower, 'engineer') !== false || strpos($roleLower, 'programmer') !== false) {
        return [
            "Can you explain the differences between relational (SQL) and non-relational (NoSQL) databases, and when to use each?",
            "How do you approach writing clean, maintainable code? Can you describe a design pattern you've used recently?",
            "Describe a challenging bug you encountered in a previous project and the technical steps you took to diagnose and resolve it.",
            "Explain the concept of asynchronous programming or promises. How do you handle concurrency in your preferred tech stack?",
            "Tell me about a time you had to work with a legacy codebase. How did you get up to speed and implement changes safely?"
        ];
    } elseif (strpos($roleLower, 'data') !== false || strpos($roleLower, 'analyst') !== false) {
        return [
            "What is your approach to handling missing data or outliers in a dataset before starting an analysis?",
            "Can you explain the difference between supervised and unsupervised machine learning models, with examples?",
            "How would you explain a complex data analysis result or statistical concept to a non-technical stakeholder?",
            "What SQL join types are there, and how do you optimize a query that is running slowly on a large dataset?",
            "Describe a data visualization dashboard you created. How did you decide which charts to use to represent the metrics?"
        ];
    } elseif (strpos($roleLower, 'manager') !== false || strpos($roleLower, 'lead') !== false) {
        return [
            "How do you handle disagreements within your team regarding technical design decisions?",
            "Describe your experience with Agile/Scrum methodologies. How do you ensure sprints are delivered on time without team burnout?",
            "Tell me about a time when a project's requirements changed drastically mid-development. How did you pivot?",
            "How do you balance technical debt against the pressure to deliver new user-facing features quickly?",
            "Describe a successful project you managed from start to finish. What key metrics did you track to define success?"
        ];
    } else {
        return [
            "What motivated you to apply for this '{$role}' position, and how does your background prepare you for success here?",
            "Tell me about a time you had to learn a new tool or technology under a tight deadline. How did you manage your learning process?",
            "Describe a project you worked on where team collaboration was key. How did you handle conflicts or differing opinions?",
            "How do you organize and prioritize your tasks when managing multiple competing deadlines?",
            "Where do you see yourself professionally in the next three years, and how does this role fit into that trajectory?"
        ];
    }
}

function getMockEvaluation($question, $answer) {
    $len = strlen(trim($answer));
    
    if ($len < 15) {
        return [
            "score" => 2,
            "feedback" => "Your answer is extremely short. Try to elaborate on your points, provide structured examples (Situation, Task, Action, Result), and dive deeper into the technical details."
        ];
    } elseif ($len < 50) {
        return [
            "score" => 4,
            "feedback" => "A bit brief. While you touched on the core topic, you should expand on the 'how' and 'why'. Mention specific technologies, methodologies, or project scenarios to ground your answer."
        ];
    }
    
    // Analyze quality based on keywords
    $goodWords = ['optimized', 'result', 'resolved', 'architected', 'scalable', 'team', 'agile', 'database', 'designed', 'tested', 'implemented'];
    $matchCount = 0;
    foreach ($goodWords as $word) {
        if (strpos(strtolower($answer), $word) !== false) {
            $matchCount++;
        }
    }
    
    $score = 5 + intval($matchCount * 0.8);
    if ($score > 10) $score = 10;
    
    $feedback = "Good response! You structured your points clearly and explained the context well. ";
    if ($score >= 8) {
        $feedback .= "Excellent depth and professional vocabulary. You did a great job connecting your actions to tangible outcomes.";
    } else {
        $feedback .= "To make this answer outstanding, try adding more concrete metrics or describing the specific constraints you faced and how you overcame them.";
    }
    
    return [
        "score" => $score,
        "feedback" => $feedback
    ];
}
