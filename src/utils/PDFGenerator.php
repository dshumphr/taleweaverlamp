<?php
namespace App\Utils;

use Mpdf\Mpdf;

class PDFGenerator {
    private $mpdf;

    public function __construct() {
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9
        ]);
    }

    public function generateStoryPDF($story) {
        try {
            // Set document metadata
            $this->mpdf->SetTitle($story['title']);
            $this->mpdf->SetAuthor('Tale Weaver');
            
            // Write CSS separately
            $this->mpdf->WriteHTML($this->getStyles(), \Mpdf\HTMLParserMode::HEADER_CSS);
            
            // Write the title on the first page
            $this->mpdf->WriteHTML("<h1 class='title'>{$story['title']}</h1>", \Mpdf\HTMLParserMode::HTML_BODY);
            $this->mpdf->AddPage();
            
            // Write each page separately
            foreach ($story['pages'] as $index => $page) {
                $pageHtml = '<div class="page">';
                $pageHtml .= '<div class="text">' . $page['text'] . '</div>';
                
                if (isset($story['illustrations'][$index])) {
                    $pageHtml .= '<img class="illustration" src="' . $story['illustrations'][$index] . '" />';
                }
                
                $pageHtml .= '</div>';
                
                $this->mpdf->WriteHTML($pageHtml, \Mpdf\HTMLParserMode::HTML_BODY);
                
                // Add a new page after each content page, except for the last one
                if ($index < count($story['pages']) - 1) {
                    $this->mpdf->AddPage();
                }
            }
            
            return $this->mpdf->Output('', 'S');
            
        } catch (\Exception $e) {
            error_log("PDF Generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function getStyles() {
        return <<<CSS
            body { 
                font-family: sans-serif;
                line-height: 1.6;
                color: #1a1a1a;
            }
            .title {
                font-size: 24pt;
                color: #78350f;
                text-align: center;
                margin-top: 50%;
                transform: translateY(-50%);
            }
            .page {
                margin-bottom: 2em;
            }
            .text {
                margin-bottom: 1em;
            }
            .illustration {
                width: 100%;
                max-height: 60%;
                object-fit: contain;
            }
        CSS;
    }
}