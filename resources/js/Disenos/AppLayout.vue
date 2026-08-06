<template>
    <div class="min-vh-100 d-flex flex-column">

        <!-- ================================================================================== -->
        <!-- COMPONENTE: AppLayout -->
        <!-- ================================================================================== -->
        
        <!-- 
            El componente "AppLayout" es el diseño principal de la aplicación. 
            Contiene la estructura base de la aplicación, incluyendo el navbar, 
            el sidebar y el contenido principal. 
            Es el componente que se encarga de renderizar el contenido de las páginas.
        -->

        <!-- Contenedor principal de la aplicación -->
        <main class="flex-grow-1">
            
            <slot />
        </main>
        
    </div>
</template>
