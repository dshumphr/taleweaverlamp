<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Utils\PDFGenerator;

session_start();

// Check if we have a story in session
if (!isset($_SESSION['story'])) {
    header('Location: index.php');
    exit;
}

try {
    $story = $_SESSION['story'];
    $text = [];
    foreach ($story['pages'] as $page) {
        $text[] = $page['text'];
    }
    
    // Initialize PDF generator
    $pdfGenerator = new PDFGenerator();
    
    // Generate PDF
    $pdfContent = $pdfGenerator->generateStoryPDF(
        $story
    );
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $story['title'] . '.pdf"');
    header('Content-Length: ' . strlen($pdfContent));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Output PDF
    echo $pdfContent;
    
} catch (Exception $e) {
    // Log error
    error_log("PDF generation failed: " . $e->getMessage());
    
    // Redirect back with error
    $_SESSION['error'] = 'Failed to generate PDF. Please try again.';
    header('Location: index.php');
    exit;
}