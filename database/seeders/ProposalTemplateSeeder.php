<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProposalTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Web Development Project',
                'type' => 'web-development',
                'description' => 'Standard template for web development projects',
                'content' => '<h1>Website Development Proposal</h1>

<h2>Project Overview</h2>
<p>We are pleased to present this proposal for the development of a new website for <strong>{{company_name}}</strong>.</p>

<h2>Scope of Work</h2>
<ul>
    <li>Custom website design and development</li>
    <li>Responsive design for all devices</li>
    <li>Content management system</li>
    <li>SEO optimization</li>
    <li>Testing and quality assurance</li>
</ul>

<h2>Timeline</h2>
<p>The project is estimated to take <strong>{{timeline}}</strong> weeks to complete.</p>

<h2>Investment</h2>
<p>Total project cost: <strong>${{amount}}</strong></p>

<h2>Terms and Conditions</h2>
<p>50% deposit required to begin work, with remaining balance due upon completion.</p>

<p>We look forward to working with you on this exciting project!</p>',
                'variables' => ['company_name', 'timeline', 'amount'],
                'is_active' => true,
            ],
            [
                'name' => 'Consulting Services',
                'type' => 'consulting',
                'description' => 'Template for consulting service proposals',
                'content' => '<h1>Business Consulting Proposal</h1>

<h2>Executive Summary</h2>
<p>This proposal outlines our consulting services for <strong>{{company_name}}</strong> to help improve {{focus_area}}.</p>

<h2>Our Approach</h2>
<p>Our consulting methodology includes:</p>
<ul>
    <li>Initial assessment and analysis</li>
    <li>Strategic planning sessions</li>
    <li>Implementation roadmap</li>
    <li>Ongoing support and monitoring</li>
</ul>

<h2>Deliverables</h2>
<p>{{deliverables}}</p>

<h2>Investment</h2>
<p>Monthly retainer: <strong>${{amount}}</strong></p>

<h2>Next Steps</h2>
<p>Upon acceptance of this proposal, we will schedule an initial kickoff meeting within one week.</p>',
                'variables' => ['company_name', 'focus_area', 'deliverables', 'amount'],
                'is_active' => true,
            ],
            [
                'name' => 'Software Development',
                'type' => 'software',
                'description' => 'Template for custom software development projects',
                'content' => '<h1>Custom Software Development Proposal</h1>

<h2>Project Description</h2>
<p>We propose to develop a custom software solution for <strong>{{company_name}}</strong> that will {{project_description}}.</p>

<h2>Technical Specifications</h2>
<ul>
    <li>{{technology_stack}}</li>
    <li>Database design and implementation</li>
    <li>User interface development</li>
    <li>API development and integration</li>
    <li>Testing and documentation</li>
</ul>

<h2>Project Phases</h2>
<ol>
    <li><strong>Analysis & Design</strong> - {{phase1_duration}}</li>
    <li><strong>Development</strong> - {{phase2_duration}}</li>
    <li><strong>Testing & Deployment</strong> - {{phase3_duration}}</li>
</ol>

<h2>Investment</h2>
<p>Total project cost: <strong>${{amount}}</strong></p>
<p>Payment schedule: 30% upfront, 40% at milestone completion, 30% upon delivery.</p>

<h2>Support & Maintenance</h2>
<p>We provide 90 days of complimentary support and maintenance following project completion.</p>',
                'variables' => ['company_name', 'project_description', 'technology_stack', 'phase1_duration', 'phase2_duration', 'phase3_duration', 'amount'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            \App\Models\ProposalTemplate::create($template);
        }
    }
}
