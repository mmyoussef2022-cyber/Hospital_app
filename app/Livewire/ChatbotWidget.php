<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ChatbotSetting;
use App\Models\ChatbotFaq;
use App\Models\ChatbotConversation;

class ChatbotWidget extends Component
{
    public $isOpen = false;
    public $messages = [];
    public $currentMessage = '';
    public $settings;
    public $faqs;
    public $userName = '';
    public $userPhone = '';
    public $showContactForm = false;

    public function mount()
    {
        $this->settings = ChatbotSetting::first();
        $this->faqs = ChatbotFaq::where('is_active', true)->get();
        
        // Add welcome message
        if ($this->settings && $this->settings->welcome_message) {
            $this->messages[] = [
                'type' => 'bot',
                'message' => $this->settings->welcome_message,
                'timestamp' => now()
            ];
        }
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage()
    {
        if (empty(trim($this->currentMessage))) {
            return;
        }

        // Add user message
        $this->messages[] = [
            'type' => 'user',
            'message' => $this->currentMessage,
            'timestamp' => now()
        ];

        // Process message and get bot response
        $response = $this->processMessage($this->currentMessage);
        
        // Add bot response
        $this->messages[] = [
            'type' => 'bot',
            'message' => $response,
            'timestamp' => now()
        ];

        $this->currentMessage = '';
    }

    public function sendQuickReply($message)
    {
        $this->currentMessage = $message;
        $this->sendMessage();
    }

    public function processMessage($message)
    {
        $message = strtolower(trim($message));

        // Check FAQs for matching keywords
        foreach ($this->faqs as $faq) {
            $keywords = explode(',', strtolower($faq->keywords ?? ''));
            foreach ($keywords as $keyword) {
                if (strpos($message, trim($keyword)) !== false) {
                    return $faq->answer;
                }
            }
        }

        // Default responses
        if (strpos($message, 'مرحبا') !== false || strpos($message, 'السلام') !== false) {
            return 'مرحباً بك! كيف يمكنني مساعدتك اليوم؟';
        }

        if (strpos($message, 'حجز') !== false || strpos($message, 'موعد') !== false) {
            return 'يمكنك حجز موعد من خلال الاتصال بنا على ' . ($this->settings->phone_primary ?? 'الرقم المتاح') . ' أو يمكنني تحويلك لواتساب للحجز المباشر.';
        }

        return 'شكراً لك على تواصلك معنا. سيقوم أحد ممثلي خدمة العملاء بالرد عليك قريباً. هل تريد التحدث معنا عبر واتساب؟';
    }

    public function showContactForm()
    {
        $this->showContactForm = true;
    }

    public function submitContactInfo()
    {
        $this->validate([
            'userName' => 'required|min:2',
            'userPhone' => 'required|min:10'
        ]);

        // Save conversation
        ChatbotConversation::create([
            'visitor_name' => $this->userName,
            'visitor_phone' => $this->userPhone,
            'messages' => json_encode($this->messages),
            'status' => 'active'
        ]);

        $this->messages[] = [
            'type' => 'bot',
            'message' => 'شكراً ' . $this->userName . '! تم حفظ بياناتك وسيتم التواصل معك قريباً.',
            'timestamp' => now()
        ];

        $this->showContactForm = false;
    }

    public function transferToWhatsApp()
    {
        if ($this->settings && $this->settings->whatsapp_number) {
            $message = urlencode('مرحباً، أريد الاستفسار عن خدماتكم');
            $whatsappUrl = "https://wa.me/{$this->settings->whatsapp_number}?text={$message}";
            
            $this->dispatch('open-whatsapp', ['url' => $whatsappUrl]);
        }
    }

    public function render()
    {
        return view('livewire.chatbot-widget');
    }
}