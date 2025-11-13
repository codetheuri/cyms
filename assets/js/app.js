document.addEventListener('DOMContentLoaded', () => {


    const loadPage = async (pageUrl) => {
        try {
         
            if (!pageUrl || pageUrl === 'pages/') {
                pageUrl = 'pages/dashboard.html';
            }
            
            const response = await fetch(pageUrl);
            if (!response.ok) {
                throw new Error(`Failed to load page: ${response.status}`);
            }
            const html = await response.text();
            document.querySelector('.main-content').innerHTML = html;
        } catch (error) {
            console.error('Error loading page:', error);
            document.querySelector('.main-content').innerHTML = `
                <div class="alert alert-danger">
                    <h4 class="alert-heading">Error</h4>
                    <p>Could not load page: <strong>${pageUrl}</strong></p>
                    <p>Please check the file name and make sure it exists.</p>
                </div>`;
        }
    };


    const loadComponent = async (url, placeholderId) => {
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Failed to load component: ${response.status}`);
            }
            const html = await response.text();
            document.querySelector(placeholderId).innerHTML = html;
        } catch (error) {
            console.error('Error loading component:', error);
        }
    };

   
    const handleNavClick = (event) => {
      
        const link = event.target.closest('[data-page]');
        
        if (link) {
            event.preventDefault(); 
            const pageUrl = link.dataset.page;
            loadPage(pageUrl);

        
            const navMenu = link.closest('.nav');
            if (navMenu) {
               
                const allNavMenus = document.querySelectorAll('#sidebar-placeholder .nav, #mobile-nav-placeholder .nav');

                allNavMenus.forEach(menu => {
                  
                    menu.querySelectorAll('.nav-link').forEach(navLink => {
                        navLink.classList.remove('active');
                    });
                    
                   
                    const matchingLink = menu.querySelector(`.nav-link[data-page="${pageUrl}"]`);
                    if (matchingLink) {
                        matchingLink.classList.add('active');
                    }
                });
            }
        }
    };

  
    const initApp = async () => {
        await Promise.all([
            loadComponent('layouts/navbar.html', '#navbar-placeholder'),
            loadComponent('layouts/sidebar.html', '#sidebar-placeholder'),
            loadComponent('layouts/mobile-nav.html', '#mobile-nav-placeholder')
        ]);

        document.addEventListener('click', handleNavClick);

     
        loadPage('pages/dashboard.html');
    };


    initApp();
});