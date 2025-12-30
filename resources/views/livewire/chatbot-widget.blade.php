<!-- Chatbot Widget -->
<div class="chatbot-widget">
    <!-- Chat Toggle Button -->
    <button class="chatbot-toggle {{ $isOpen ? 'active' : '' }}" 
            wire:click="toggleChat"
            aria-label="فتح الشات بوت">
        @if($isOpen)
            <i class="fas fa-times"></i>
        @else
            <i class="fas fa-comments"></i>
            <span class="notification-badge">!</span>
        @endif
    </button>
    
    <!-- Chat Window -->
    <div class="chatbot-window {{ $isOpen ? 'open' : '' }}">
        <!-- Chat Header -->
        <div class="chatbot-header">
            <div class="d-flex align-items-center">
                <div class="chatbot-avatar me-3">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-white">مساعد المركز الذكي</h6>
                    <small class="text-white-50">متاح الآن للمساعدة</small>
                </div>
            </div>
            <button class="btn btn-sm text-white" wire:click="toggleChat">
                <i class="fas fa-minus"></i>
            </button>
        </div>
        
        <!-- Chat Messages -->
        <div class="chatbot-messages" id="chatbot-messages">
            @foreach($messages as $message)
                <div class="message {{ $message['type'] }}">
                    <div class="message-content">
                        <div class="message-text">{{ $message['message'] }}</div>
                        <div class="message-time">
                            {{ \Carbon\Carbon::parse($message['timestamp'])->format('H:i') }}
                        </div>
                    </div>
                </div>
            @endforeach
            
            @if($showContactForm)
                <div class="message bot">
                    <div class="message-content">
                        <div class="contact-form">
                            <h6 class="mb-3">معلومات التواصل</h6>
                            <form wire:submit.prevent="submitContactInfo">
                                <div class="mb-3">
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           wire:model="userName" 
                                           placeholder="الاسم الكامل" 
                                           required>
                                    @error('userName') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="mb-3">
                                    <input type="tel" 
                                           class="form-control form-control-sm" 
                                           wire:model="userPhone" 
                                           placeholder="رقم الهاتف" 
                                           required>
                                    @error('userPhone') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                        <i class="fas fa-paper-plane me-1"></i>
                                        إرسال
                                    </button>
                                    <button type="button" 
                                            class="btn btn-secondary btn-sm" 
                                            wire:click="$set('showContactForm', false)">
                                        إلغاء
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Quick Replies -->
        @if(!$showContactForm && count($faqs) > 0)
            <div class="quick-replies">
                <div class="quick-replies-title">أسئلة شائعة:</div>
                <div class="quick-replies-buttons">
                    @foreach($faqs->take(4) as $faq)
                        <button class="quick-reply-btn" 
                                wire:click="sendQuickReply('{{ $faq->question }}')">
                            {{ Str::limit($faq->question, 30) }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Chat Input -->
        <div class="chatbot-input">
            <form wire:submit.prevent="sendMessage" class="d-flex gap-2">
                <input type="text" 
                       class="form-control" 
                       wire:model="currentMessage" 
                       placeholder="اكتب رسالتك هنا..."
                       autocomplete="off">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            
            <!-- Action Buttons -->
            <div class="chat-actions mt-2">
                <button class="btn btn-outline-success btn-sm" wire:click="transferToWhatsApp">
                    <i class="fab fa-whatsapp me-1"></i>
                    واتساب
                </button>
                <button class="btn btn-outline-info btn-sm" wire:click="showContactForm">
                    <i class="fas fa-user me-1"></i>
                    معلوماتي
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .chatbot-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        font-family: 'Tajawal', sans-serif;
    }
    
    .chatbot-toggle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #007bff, #0056b3);
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .chatbot-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
    }
    
    .chatbot-toggle.active {
        background: linear-gradient(135deg, #dc3545, #c82333);
    }
    
    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 20px;
        height: 20px;
        background: #dc3545;
        border-radius: 50%;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    .chatbot-window {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 350px;
        height: 500px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        transform: scale(0) translateY(20px);
        opacity: 0;
        transition: all 0.3s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .chatbot-window.open {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
    
    .chatbot-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .chatbot-avatar {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    .chatbot-messages {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background: #f8f9fa;
    }
    
    .message {
        margin-bottom: 15px;
        display: flex;
    }
    
    .message.user {
        justify-content: flex-end;
    }
    
    .message.bot {
        justify-content: flex-start;
    }
    
    .message-content {
        max-width: 80%;
        padding: 10px 15px;
        border-radius: 18px;
        position: relative;
    }
    
    .message.user .message-content {
        background: #007bff;
        color: white;
        border-bottom-right-radius: 5px;
    }
    
    .message.bot .message-content {
        background: white;
        color: #333;
        border: 1px solid #e9ecef;
        border-bottom-left-radius: 5px;
    }
    
    .message-text {
        margin-bottom: 5px;
        line-height: 1.4;
    }
    
    .message-time {
        font-size: 11px;
        opacity: 0.7;
        text-align: right;
    }
    
    .contact-form {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }
    
    .quick-replies {
        padding: 10px 15px;
        border-top: 1px solid #e9ecef;
        background: white;
    }
    
    .quick-replies-title {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 8px;
    }
    
    .quick-replies-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .quick-reply-btn {
        background: #e9ecef;
        border: none;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        color: #495057;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .quick-reply-btn:hover {
        background: #007bff;
        color: white;
    }
    
    .chatbot-input {
        padding: 15px;
        border-top: 1px solid #e9ecef;
        background: white;
    }
    
    .chat-actions {
        display: flex;
        gap: 5px;
    }
    
    .chat-actions .btn {
        font-size: 11px;
        padding: 4px 8px;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .chatbot-window {
            width: 300px;
            height: 450px;
        }
        
        .chatbot-widget {
            bottom: 15px;
            right: 15px;
        }
    }
    
    /* Scrollbar Styling */
    .chatbot-messages::-webkit-scrollbar {
        width: 4px;
    }
    
    .chatbot-messages::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .chatbot-messages::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }
    
    .chatbot-messages::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto scroll to bottom when new messages arrive
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-whatsapp', (event) => {
            window.open(event.url, '_blank');
        });
    });
    
    // Auto scroll to bottom
    function scrollToBottom() {
        const messagesContainer = document.getElementById('chatbot-messages');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
    
    // Scroll to bottom when component updates
    document.addEventListener('livewire:updated', () => {
        setTimeout(scrollToBottom, 100);
    });
    
    // Initial scroll to bottom
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(scrollToBottom, 500);
    });
    
    // Handle Enter key in input
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.matches('.chatbot-input input')) {
            e.preventDefault();
            e.target.closest('form').dispatchEvent(new Event('submit'));
        }
    });
</script>
@endpush