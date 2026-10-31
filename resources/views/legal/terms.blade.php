@extends('layouts.public')

@section('title', 'Terms of Service - VoiceAgents')

@section('content')
    <div class="max-w-4xl mx-auto px-6 mb-24 relative z-10">
        <div
            class="bg-white rounded-2xl border border-zinc-200 shadow-2xl shadow-zinc-200/50 overflow-hidden ring-1 ring-zinc-950/5">
            <div class="px-8 py-10 sm:px-12 border-b border-zinc-100">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-14 h-14 rounded-xl bg-zinc-900 flex items-center justify-center shadow-lg flex-shrink-0">
                        <i data-lucide="file-text" class="w-7 h-7 text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-4xl font-bold tracking-tight text-zinc-900 mb-2">Terms of Service</h1>
                        <p class="text-sm text-zinc-500 font-medium">Last updated: {{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>

            <div
                class="px-8 py-10 sm:px-12 prose prose-zinc prose-headings:text-zinc-900 prose-h4:text-xl prose-h4:font-bold prose-h4:mt-10 prose-h4:mb-5 prose-h4:pb-3 prose-h4:border-b prose-h4:border-zinc-200 prose-p:text-zinc-700 prose-p:leading-relaxed prose-p:text-[15px] prose-ul:text-zinc-700 prose-ul:text-[15px] prose-li:my-1.5 prose-strong:text-zinc-900 prose-strong:font-semibold prose-a:font-medium max-w-none">
                <h4>1. Acceptance of Terms</h4>
                <p>Welcome to VoiceAgents. By accessing or using our AI-powered voice agent service ("Service"), you agree
                    to be bound by these Terms of Service ("Terms"). If you disagree with any part of these terms, you may
                    not access the Service.</p>

                <h4>2. Description of Service</h4>
                <p>VoiceAgents provides an AI-powered voice receptionist platform that enables businesses to automate
                    customer interactions through phone calls. The Service utilizes artificial intelligence, natural
                    language processing, speech recognition, and text-to-speech technologies to simulate human-like
                    conversations.</p>

                <h4>3. User Responsibilities</h4>
                <p>You agree to use the Service only for lawful purposes and in accordance with these Terms. You are
                    responsible for:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Ensuring your use of AI-generated content complies with all applicable laws and regulations</li>
                    <li>Obtaining necessary consents from individuals whose voices may be recorded or processed</li>
                    <li>Maintaining the confidentiality of your account credentials</li>
                    <li>All activity that occurs under your account</li>
                </ul>

                <h4>4. Voice Recording & Data Processing</h4>
                <p><strong>Critical:</strong> The Service involves recording and processing voice data. By using
                    VoiceAgents, you represent and warrant that:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>You have obtained all necessary consents from callers whose voices will be recorded and processed
                    </li>
                    <li>You will inform callers that their conversation is being handled by an AI system</li>
                    <li>You comply with all applicable call recording and data protection laws in your jurisdiction</li>
                </ul>

                <h4>5. Third-Party Services</h4>
                <p>Our Service integrates with third-party AI service providers for speech processing and voice synthesis.
                    Your use of the Service constitutes acceptance of their respective terms and privacy policies.</p>

                <h4>6. Payment & Subscription</h4>
                <p>Certain features of the Service require payment. By subscribing:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>You agree to pay all fees associated with your subscription plan</li>
                    <li>Payments are processed via M-Pesa and other supported payment methods</li>
                    <li>Subscription fees are billed in advance on a recurring basis</li>
                    <li>Refunds are handled on a case-by-case basis as per our refund policy</li>
                </ul>

                <h4>7. Intellectual Property</h4>
                <p>The Service and its original content, features, and functionality are owned by VoiceAgents and are
                    protected by international copyright, trademark, and other intellectual property laws.</p>

                <h4>8. Limitation of Liability</h4>
                <p>To the maximum extent permitted by law, VoiceAgents shall not be liable for any indirect, incidental,
                    special, consequential, or punitive damages resulting from your use or inability to use the Service.</p>

                <h4>9. Service Availability</h4>
                <p>We strive to provide reliable service but cannot guarantee uninterrupted access. We reserve the right to
                    modify, suspend, or discontinue the Service at any time without prior notice.</p>

                <h4>10. Termination</h4>
                <p>We reserve the right to terminate or suspend your account and access to the Service immediately, without
                    prior notice or liability, for any reason, including but not limited to breach of these Terms.</p>

                <h4>11. Changes to Terms</h4>
                <p>We reserve the right to modify these Terms at any time. We will notify users of significant changes via
                    email or through the Service. Continued use after changes constitutes acceptance of modified Terms.</p>

                <h4>12. Governing Law</h4>
                <p>These Terms shall be governed by and construed in accordance with the laws of Kenya, without regard to
                    its conflict of law provisions.</p>

                <h4>13. Contact Information</h4>
                <p>If you have any questions about these Terms of Service, please contact us at <a
                        href="mailto:support@voiceagents.com"
                        class="text-emerald-600 hover:text-emerald-500 font-medium">support@voiceagents.com</a></p>
            </div>

            <div class="px-8 py-6 sm:px-12 bg-zinc-50 border-t border-zinc-100">
                <a href="{{ url('/') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-900 hover:text-emerald-600 transition-colors group">
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
@endsection