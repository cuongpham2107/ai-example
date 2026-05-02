<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat - Tích hợp MCP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dark .glass {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .chat-container {
            height: calc(100vh - 180px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }

        /* Markdown Styles */
        .prose {
            font-size: 0.875rem;
            line-height: 1.6;
        }
        .prose p { margin-bottom: 0.5rem; }
        .prose p:last-child { margin-bottom: 0; }
        .prose ul, .prose ol { margin-bottom: 0.5rem; padding-left: 1.25rem; }
        .prose ul { list-style-type: disc; }
        .prose ol { list-style-type: decimal; }
        .prose li { margin-bottom: 0.25rem; }
        .prose strong { font-weight: 700; color: inherit; }
        .prose h1, .prose h2, .prose h3 { font-weight: 700; margin-top: 1rem; margin-bottom: 0.5rem; }
        .prose code { background: rgba(0,0,0,0.05); padding: 0.2rem 0.4rem; rounded: 0.25rem; font-family: monospace; }
        .dark .prose code { background: rgba(255,255,255,0.1); }
        .prose pre { background: #1e293b; color: #f8fafc; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 0.5rem 0; }
        .prose blockquote { border-left: 4px solid #e2e8f0; padding-left: 1rem; font-style: italic; color: #64748b; }
        .dark .prose blockquote { border-left-color: #334155; color: #94a3b8; }
    </style>
</head>

<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-300"
    x-data="chatApp()" :class="{ 'dark': darkMode }">

    <div class="fixed inset-0 bg-linear-to-br from-blue-500/10 to-purple-500/10 pointer-events-none"></div>

    <div class="relative h-full flex flex-col max-w-5xl mx-auto px-4 py-8">
        <!-- Header -->
        <header class="flex items-center justify-between mb-8 glass rounded-2xl px-6 py-4 shadow-xl">
            <div class="flex items-center space-x-3">
                <div
                    class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">Trợ lý AI</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Hỗ trợ bởi MCP • Trực tuyến</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="darkMode = !darkMode"
                    class="p-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <template x-if="!darkMode">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </template>
                    <template x-if="darkMode">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.95 17.657l.707.707M6.343 6.343l.707-.707" />
                        </svg>
                    </template>
                </button>
            </div>
        </header>

        <!-- Chat Area -->
        <main class="flex-1 overflow-hidden glass rounded-3xl shadow-2xl flex flex-col relative mb-6">
            <div class="flex-1 overflow-y-auto px-6 py-8 space-y-6 scroll-smooth" id="message-container">
                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'"
                        class="animate-fade-in">
                        <div :class="msg.role === 'user' ?
                            'bg-blue-600 text-white rounded-t-2xl rounded-bl-2xl shadow-lg shadow-blue-500/20' :
                            'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-t-2xl rounded-br-2xl shadow-sm border border-slate-100 dark:border-slate-700'"
                            class="max-w-[85%] px-5 py-3.5 relative">
                            <div class="prose dark:prose-invert max-w-none" x-html="renderMarkdown(msg.content)"></div>
                            <span class="text-[10px] opacity-50 mt-1 block text-right" x-text="msg.time"></span>
                        </div>
                    </div>
                </template>

                <div x-show="isTyping" class="flex justify-start animate-fade-in">
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl px-5 py-3 shadow-sm border border-slate-100 dark:border-slate-700">
                        <div class="flex space-x-1.5">
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0s">
                            </div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.2s">
                            </div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.4s">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-6 border-t border-slate-100 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50">
                <form @submit.prevent="sendMessage" class="relative">
                    <input type="text" x-model="userInput" placeholder="Nhập tin nhắn của bạn ở đây..."
                        class="w-full bg-white dark:bg-slate-800 border-none rounded-2xl pl-6 pr-16 py-4 shadow-inner focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all"
                        :disabled="isTyping">
                    <button type="submit"
                        class="absolute right-2 top-2 bottom-2 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-95 flex items-center justify-center"
                        :disabled="isTyping || !userInput.trim()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
            </div>
        </main>

        <footer class="text-center text-slate-400 text-xs font-medium">
            &copy; {{ date('Y') }} Dự án AI Example • Xây dựng với Laravel AI SDK
        </footer>
    </div>

    <script>
        function chatApp() {
            return {
                darkMode: window.matchMedia('(prefers-color-scheme: dark)').matches,
                userInput: '',
                isTyping: false,
                messages: [{
                    role: 'assistant',
                    content: 'Xin chào! Tôi là trợ lý AI của bạn. Tôi có thể giúp bạn tra cứu thông tin nhân sự bằng các công cụ MCP tích hợp. Hãy thử hỏi "Lấy thông tin cho nhân viên có mã ID 123"',
                    time: new Date().toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    })
                }],
                renderMarkdown(content) {
                    return marked.parse(content);
                },
                async sendMessage() {
                    if (!this.userInput.trim()) return;

                    const userMessage = this.userInput;
                    this.userInput = '';

                    this.messages.push({
                        role: 'user',
                        content: userMessage,
                        time: new Date().toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        })
                    });

                    this.scrollToBottom();
                    this.isTyping = true;

                    try {
                        const response = await fetch('/chat/send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                message: userMessage,
                                history: this.messages
                            })
                        });

                        const data = await response.json();

                        this.messages.push({
                            role: 'assistant',
                            content: data.message,
                            time: new Date().toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            })
                        });
                    } catch (error) {
                        console.error('Error:', error);
                        this.messages.push({
                            role: 'assistant',
                            content: 'Rất tiếc, tôi đã gặp lỗi. Vui lòng thử lại.',
                            time: new Date().toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            })
                        });
                    } finally {
                        this.isTyping = false;
                        this.scrollToBottom();
                    }
                },
                scrollToBottom() {
                    setTimeout(() => {
                        const container = document.getElementById('message-container');
                        container.scrollTop = container.scrollHeight;
                    }, 50);
                }
            }
        }
    </script>
</body>

</html>
