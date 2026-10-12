<header class="main-header">
    <!-- Navbar -->
    <nav class="navbar navbar-expand navbar-dark bg-dark">
    
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      
        <!-- Fullscreen Button -->
        <div class="ml-auto">
       <button id="fullscreenButton" class="btn btn-primary">
            <i class="fa fa-arrows-alt" aria-hidden="true"></i>
        </button>
</div>

        <!-- Login Icon aligned to the right -->
        <div class="ml-auto">
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf  <!-- CSRF token for protection -->
                <button type="submit" class="btn btn-link text-white" title="Logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </nav>
</header>
