<?php
/**
 * NEETSTACK SEO ANALYZER v2.0
 * Returns a detailed SEO score breakdown — no AI call needed,
 * pure algorithmic analysis of the article content.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../auth.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { echo json_encode(['error' => 'No input']); exit; }

$title    = strip_tags($input['title']   ?? '');
$meta     = strip_tags($input['meta']    ?? '');
$desc     = strip_tags($input['desc']    ?? '');
$content  = $input['content'] ?? '';
$keyword  = strtolower(trim($input['keyword'] ?? ''));
$slug     = $input['slug']   ?? '';
$schema   = $input['schema'] ?? '';
$chatbot  = $input['chatbot'] ?? '';

$plain    = strtolower(strip_tags($content));
$words    = str_word_count($plain);
$scores   = [];
$tips     = [];

// ── Title ─────────────────────────────────────────────────────────────────────
$tLen = mb_strlen($title);
if ($keyword && stripos($title, $keyword) !== false) {
    $scores['title_keyword'] = 10;
} else {
    $scores['title_keyword'] = 0;
    $tips[] = "❌ Focus keyword missing from title.";
}
if ($tLen >= 40 && $tLen <= 65) {
    $scores['title_length'] = 5;
} elseif ($tLen > 0) {
    $scores['title_length'] = 2;
    $tips[] = "⚠️ Title length is {$tLen} chars. Ideal: 40–65.";
} else {
    $scores['title_length'] = 0;
    $tips[] = "❌ Title is empty.";
}

// ── Meta Description ──────────────────────────────────────────────────────────
$dLen = mb_strlen($desc);
if ($keyword && stripos($desc, $keyword) !== false) {
    $scores['meta_keyword'] = 8;
} else {
    $scores['meta_keyword'] = 0;
    $tips[] = "❌ Focus keyword missing from meta description.";
}
if ($dLen >= 130 && $dLen <= 165) {
    $scores['meta_length'] = 5;
} elseif ($dLen > 0) {
    $scores['meta_length'] = 2;
    $tips[] = "⚠️ Meta description is {$dLen} chars. Ideal: 130–160.";
} else {
    $scores['meta_length'] = 0;
    $tips[] = "❌ Meta description is empty.";
}

// ── Content Length ────────────────────────────────────────────────────────────
if ($words >= 1500) {
    $scores['content_length'] = 15;
} elseif ($words >= 1000) {
    $scores['content_length'] = 10;
    $tips[] = "⚠️ Article is {$words} words. Aim for 1500+ for top ranking.";
} elseif ($words >= 500) {
    $scores['content_length'] = 5;
    $tips[] = "⚠️ Short article ({$words} words). Add more depth.";
} else {
    $scores['content_length'] = 0;
    $tips[] = "❌ Article too short ({$words} words). Needs major expansion.";
}

// ── Keyword Density ───────────────────────────────────────────────────────────
if ($keyword && $words > 0) {
    $kwCount = substr_count($plain, $keyword);
    $density = $kwCount / $words * 100;
    if ($density >= 0.5 && $density <= 2.5) {
        $scores['keyword_density'] = 15;
    } elseif ($density > 0) {
        $scores['keyword_density'] = 7;
        $tips[] = sprintf("⚠️ Keyword density %.1f%% (found %d times). Ideal: 0.5–2.5%%.", $density, $kwCount);
    } else {
        $scores['keyword_density'] = 0;
        $tips[] = "❌ Focus keyword not found in article body.";
    }
} else {
    $scores['keyword_density'] = 0;
    $tips[] = "❌ No focus keyword set.";
}

// ── Headings ──────────────────────────────────────────────────────────────────
$h2Count = substr_count(strtolower($content), '<h2');
$h3Count = substr_count(strtolower($content), '<h3');
if ($h2Count >= 3 && $h3Count >= 2) {
    $scores['headings'] = 10;
} elseif ($h2Count >= 2) {
    $scores['headings'] = 6;
    $tips[] = "⚠️ Add more H2/H3 headings. Found: {$h2Count} H2, {$h3Count} H3.";
} elseif ($h2Count >= 1) {
    $scores['headings'] = 3;
    $tips[] = "❌ Very few headings. Add at least 3 H2 sections.";
} else {
    $scores['headings'] = 0;
    $tips[] = "❌ No H2 headings found. Headings are essential for SEO.";
}

// Check if keyword in first H2
if ($keyword && preg_match('/<h2[^>]*>(.*?)<\/h2>/is', $content, $m)) {
    if (stripos($m[1], $keyword) !== false) {
        $scores['keyword_in_h2'] = 5;
    } else {
        $scores['keyword_in_h2'] = 2;
        $tips[] = "⚠️ Consider including focus keyword in the first H2.";
    }
} else {
    $scores['keyword_in_h2'] = 0;
}

// ── Schema Markup ─────────────────────────────────────────────────────────────
if (!empty($schema)) {
    $schemaData = json_decode($schema, true);
    if ($schemaData) {
        $scores['schema'] = 10;
        if (is_string($schemaData['@type'] ?? null) && in_array($schemaData['@type'], ['Article','NewsArticle','BlogPosting'])) {
            $scores['schema'] += 2;
        }
    } else {
        $scores['schema'] = 4;
        $tips[] = "⚠️ Schema markup exists but is invalid JSON.";
    }
} else {
    $scores['schema'] = 0;
    $tips[] = "❌ No JSON-LD schema markup. Add Article + FAQPage schema.";
}

// ── Slug ──────────────────────────────────────────────────────────────────────
if ($keyword && stripos($slug, str_replace(' ', '-', $keyword)) !== false) {
    $scores['slug'] = 5;
} elseif (!empty($slug)) {
    $scores['slug'] = 3;
    $tips[] = "⚠️ URL slug doesn't contain focus keyword.";
} else {
    $scores['slug'] = 0;
    $tips[] = "❌ URL slug is empty.";
}

// ── Lists & Tables ────────────────────────────────────────────────────────────
$hasList  = stripos($content, '<ul') !== false || stripos($content, '<ol') !== false;
$hasTable = stripos($content, '<table') !== false;
$scores['lists'] = ($hasList ? 3 : 0) + ($hasTable ? 2 : 0);
if (!$hasList) $tips[] = "⚠️ Add bullet lists or numbered lists for readability & featured snippets.";
if (!$hasTable) $tips[] = "💡 Consider adding a comparison table for featured snippet eligibility.";

// ── FAQ Section ───────────────────────────────────────────────────────────────
$hasFaq = stripos($content, 'faq') !== false || stripos($content, 'frequently asked') !== false;
$scores['faq'] = $hasFaq ? 5 : 0;
if (!$hasFaq) $tips[] = "💡 Add an FAQ section to target featured snippets and voice search.";

// ── Chatbot Context ───────────────────────────────────────────────────────────
$cbWords = str_word_count(strip_tags($chatbot));
if ($cbWords >= 200) {
    $scores['chatbot'] = 5;
} elseif ($cbWords > 0) {
    $scores['chatbot'] = 2;
    $tips[] = "⚠️ AI chatbot context is short ({$cbWords} words). Aim for 300+.";
} else {
    $scores['chatbot'] = 0;
    $tips[] = "❌ No chatbot context. AI chatbots won't index this article well.";
}

// ── Readability: Paragraph length ─────────────────────────────────────────────
preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $content, $pMatches);
$longParas = 0;
foreach ($pMatches[1] as $p) {
    if (str_word_count(strip_tags($p)) > 120) $longParas++;
}
if ($longParas === 0 && count($pMatches[1]) > 0) {
    $scores['readability'] = 5;
} elseif ($longParas <= 1) {
    $scores['readability'] = 3;
    $tips[] = "⚠️ {$longParas} paragraph(s) are very long. Break them up for readability.";
} else {
    $scores['readability'] = 1;
    $tips[] = "❌ Multiple long paragraphs. Split content into shorter blocks.";
}

// ── First Paragraph / Lead ───────────────────────────────────────────────────
if ($keyword && count($pMatches[1]) > 0) {
    $firstPara = strtolower(strip_tags($pMatches[1][0]));
    if (stripos($firstPara, $keyword) !== false) {
        $scores['first_para_kw'] = 5;
    } else {
        $scores['first_para_kw'] = 0;
        $tips[] = "❌ Focus keyword not in the opening paragraph. Add it naturally.";
    }
} else {
    $scores['first_para_kw'] = 0;
}

// ── Blockquote ───────────────────────────────────────────────────────────────
$scores['blockquote'] = (stripos($content, '<blockquote') !== false) ? 3 : 0;
if (!$scores['blockquote']) $tips[] = "💡 Add a blockquote with an expert insight or key statistic.";

// ── Total Score ───────────────────────────────────────────────────────────────
$total = min(100, array_sum($scores));

// ── Grade ─────────────────────────────────────────────────────────────────────
$grade = match(true) {
    $total >= 90 => ['label' => 'Outstanding',  'color' => '#16a34a'],
    $total >= 75 => ['label' => 'Good',          'color' => '#65a30d'],
    $total >= 60 => ['label' => 'Needs Work',    'color' => '#d97706'],
    $total >= 40 => ['label' => 'Poor',          'color' => '#dc2626'],
    default      => ['label' => 'Critical',      'color' => '#7f1d1d'],
};

echo json_encode([
    'score'      => $total,
    'grade'      => $grade,
    'breakdown'  => $scores,
    'tips'       => $tips,
    'stats'      => [
        'words'      => $words,
        'h2_count'   => $h2Count,
        'h3_count'   => $h3Count,
        'title_len'  => $tLen,
        'desc_len'   => $dLen,
        'kw_count'   => $kwCount ?? 0,
    ],
]);
