<template>
    <!-- ================================================================================== -->
    <!-- COMPONENTE: BotonChatbotn8n -->
    <!-- ================================================================================== -->
    <div>
        <!-- Se dispara la acción "toggleChat" al dar click -->
        <button 
            class="n8n-chat-fab"
            @click="toggleChat"
            aria-label="Abrir asistente de chat"
        >
            <span class="pulse-ring"></span>
            <img src="/images/chatbot_avatar.png" alt="Avatar Chatbot" class="chat-fab-avatar">
        </button>

        <!-- Ventana del chat -->
        <transition name="slide-fade">
            <div v-show="isOpen" class="n8n-chat-window">
                <!-- Encabezado -->
                <div class="n8n-chat-header">
                    <div class="header-info">
                        <div class="avatar-container">
                            <span class="status-indicator online"></span>
                            <img src="/images/chatbot_avatar.png" alt="Avatar Chatbot" class="avatar-img">
                        </div>
                        <div>
                            <h4 class="chat-title">Asistente Virtual</h4>
                            <p class="chat-subtitle">En línea • Soporte Inteligente</p>
                        </div>
                    </div>
                    <!-- Dispara la acción "closeChat" al dar click -->
                    <button class="close-btn" @click="closeChat" aria-label="Cerrar chat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <!-- Contenedor del chat para n8n -->
                <div id="n8n-chat-container" class="n8n-chat-body"></div>
            </div>
        </transition>
    </div>
</template>

<script>
// ==================================================================================
// LÓGICA DEL COMPONENTE (VUE 3)
// ==================================================================================

// ------------------------------------------------------------------------------
// EXPORT DEFAULT: Definición principal del componente
// ------------------------------------------------------------------------------
export default {
    name: 'BotonChatbotn8n',
    // PROPIEDADES: Datos inyectados desde el componente padre o estado
    props: {
        // URL del webhook de n8n
        webhookUrl: {
            type: String,
            default: 'http://localhost:5678/webhook/7e44df6c-3e7f-4fe6-b885-d97be1daea48/chat'
        }
    },
    // ESTADO REACTIVO: Variables locales del componente
    data() {
        return {
            // Controla la visibilidad de la ventana del chat
            isOpen: false,
            // Controla la inicialización del chat
            chatInitialized: false
        };
    },
    // CICLO DE VIDA (MOUNTED): Se ejecuta al cargar el componente en el DOM
    mounted() {
        // Carga los activos de n8n
        this.loadN8nAssets();
    },
    // MÉTODOS (METHODS): Bloque de funciones y eventos
    methods: {
        //Carga los activos de n8n
        loadN8nAssets() {
            // Si ya se cargaron los activos, no hacer nada
            if (document.getElementById('n8n-chat-css')) return;
            
            // Crear el elemento link para el CSS
            const link = document.createElement('link');
            link.id = 'n8n-chat-css';
            link.rel = 'stylesheet';
            link.href = 'https://cdn.jsdelivr.net/npm/@n8n/chat/dist/style.css';
            document.head.appendChild(link);
        },
        // Inicializa el chat de forma asíncrona para no bloquear el hilo principal
        async initChat() {
            // Si el chat ya está inicializado, no hacer nada
            if (this.chatInitialized) return;
            
            // Intenta inicializar el chat
            try {
                // Importa el módulo de chat de n8n
                const module = await import('https://cdn.jsdelivr.net/npm/@n8n/chat/dist/chat.bundle.es.js');

                // Extrae la función createChat del módulo para poder crear el chat
                const { createChat } = module;
                
                // Crea el chat de n8n
                createChat({

                    webhookUrl: this.webhookUrl,

                    target: '#n8n-chat-container',
                    mode: 'fullscreen',
                    showWelcomeScreen: true,
                    metadata: {
                        usuario_id: this.$page.props.auth.user?.id || null
                    },
                    i18n: {
                        en: {
                            title: 'Asistente Virtual',
                            inputPlaceholder: 'Escribe tu mensaje aquí...',
                        }
                    }
                });
                
                this.chatInitialized = true;
            } catch (error) {
                // El manejo de errores se hace silenciosamente para mantener los logs limpios
            }
        },
        // Alterna la visibilidad del chat
        toggleChat() {
            this.isOpen = !this.isOpen;
            // Si el chat está abierto, inicializa el chat
            if (this.isOpen) {
                // Espera a que el DOM se actualice para poder inicializar el chat
                this.$nextTick(() => {
                    this.initChat();
                });
            }
        },
        // Cierra el chat
        closeChat() {
            this.isOpen = false;
        }
    }
};
</script>

<style scoped>
.n8n-chat-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border: none;
    color: white;
    box-shadow: 0 8px 30px rgba(124, 58, 237, 0.4);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.n8n-chat-fab:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 12px 35px rgba(124, 58, 237, 0.6);
}

.pulse-ring {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 2px solid rgba(124, 58, 237, 0.5);
    animation: pulse 2s infinite;
    pointer-events: none;
}

@keyframes pulse {
    0% {
        transform: scale(0.95);
        opacity: 1;
    }
    100% {
        transform: scale(1.4);
        opacity: 0;
    }
}

.chat-fab-avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
    transition: transform 0.3s ease;
}

.n8n-chat-fab:active .chat-fab-avatar {
    transform: scale(0.9);
}

.n8n-chat-window {
    position: fixed;
    bottom: 100px;
    right: 24px;
    width: 400px;
    height: 600px;
    max-height: calc(100vh - 120px);
    max-width: calc(100vw - 48px);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 9998;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.n8n-chat-header {
    padding: 16px 20px;
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar-container {
    position: relative;
    width: 40px;
    height: 40px;
}

.avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 12px;
    object-fit: cover;
    background: rgba(255, 255, 255, 0.15);
}

.status-indicator {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #1e1b4b;
}

.status-indicator.online {
    background-color: #10b981;
    box-shadow: 0 0 8px #10b981;
}

.chat-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    letter-spacing: -0.01em;
}

.chat-subtitle {
    margin: 0;
    font-size: 11px;
    color: #a5b4fc;
}

.close-btn {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
    padding: 4px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.n8n-chat-body {
    flex: 1;
    width: 100%;
    height: 100%;
    background: #f8fafc;
    overflow: hidden;
    position: relative;
}

/* Animations */
.slide-fade-enter-active {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.slide-fade-leave-active {
    transition: all 0.25s cubic-bezier(0.36, 0.07, 0.19, 0.97);
}

.slide-fade-enter-from {
    transform: translateY(30px) scale(0.9);
    opacity: 0;
}

.slide-fade-leave-to {
    transform: translateY(20px) scale(0.95);
    opacity: 0;
}

/* Override n8n widget styles inside the container to match our design */
:deep(.n8n-chat-widget) {
    height: 100% !important;
    width: 100% !important;
    box-shadow: none !important;
    border: none !important;
    border-radius: 0 !important;
}

:deep(.n8n-chat-widget__header) {
    display: none !important; /* Ocultamos la cabecera por defecto de n8n para usar la nuestra premium */
}

:deep(.n8n-chat-widget__messages) {
    background: #f8fafc !important;
}
</style>
