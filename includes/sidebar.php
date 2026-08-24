    <!--begin::Header-->
    <nav class="app-header navbar navbar-expand bg-body">
      <!--begin::Container-->
      <div class="container-fluid">
        <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
              <i class="bi bi-list"></i>
            </a>
          </li>

          <li class="nav-item d-none d-md-flex align-items-center">
            <span class="nav-link fw-semibold">
              PACHECO'S MOTO SERVICE
            </span>
          </li>
        </ul>
        <!--end::Start Navbar Links-->

        <!--begin::End Navbar Links-->
        <ul class="navbar-nav ms-auto">

          <!--begin::Fullscreen Toggle-->
          <li class="nav-item">
            <a class="nav-link" href="#" data-lte-toggle="fullscreen">
              <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
              <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
            </a>
          </li>
          <!--end::Fullscreen Toggle-->

          <!--begin::User Menu Dropdown-->
          <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
              <!-- <img
                src="./adminlte/dist/assets/img/user2-160x160.jpg"
                class="user-image rounded-circle shadow"
                alt="User Image" /> -->
              <span class="d-none d-md-inline"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <!--begin::User Image-->
              <li class="user-header text-bg-primary">
                <!--  <img
                  src="./adminlte/dist/assets/img/user2-160x160.jpg"
                  class="rounded-circle shadow"
                  alt="User Image" /> -->
                <p>
                  Alexander Pierce - Web Developer
                  <small></small>
                </p>
              </li>
              <!--end::User Image-->
              <!--begin::Menu Body-->
              <li class="user-body">
                <!--begin::Row-->
                <div class="row">
                  <div class="col-4 text-center">
                    <a href="#">Followers</a>
                  </div>
                  <div class="col-4 text-center">
                    <a href="#">Sales</a>
                  </div>
                  <div class="col-4 text-center">
                    <a href="#">Friends</a>
                  </div>
                </div>
                <!--end::Row-->
              </li>
              <!--end::Menu Body-->
              <!--begin::Menu Footer-->
              <li class="user-footer">
                <a href="#" class="btn btn-outline-secondary">Profile</a>
                <a href="#" class="btn btn-outline-danger float-end">Sign out</a>
              </li>
              <!--end::Menu Footer-->
            </ul>
          </li>
          <!--end::User Menu Dropdown-->
        </ul>
        <!--end::End Navbar Links-->
      </div>
      <!--end::Container-->
    </nav>
    <!--end::Header-->
    <!--begin::Sidebar-->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
      <!--begin::Sidebar Brand-->
      <div class="sidebar-brand">
        <a href="index.php" class="brand-link">

          <img
            src="img/logo-pachecos.png"
            alt="Pacheco's Moto Service"
            class="brand-image pachecos-brand-logo">

          <span class="brand-text fw-semibold">
            PACHECO'S
          </span>

        </a>
      </div>
      <!--end::Sidebar Brand-->
      <!--begin::Sidebar Wrapper-->
      <div class="sidebar-wrapper">
        <nav class="mt-2">
          <!--begin::Sidebar Menu-->
          <ul
            class="nav sidebar-menu flex-column"
            data-lte-toggle="treeview"
            role="navigation"
            aria-label="Main navigation"
            data-accordion="false"
            id="navigation">
            <li class="nav-item">
              <a href="index.php" class="nav-link active">
                <i class="nav-icon bi bi-speedometer"></i>
                <p>Dashboard</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="clientes.php" class="nav-link">
                <i class="nav-icon bi bi-person"></i>
                <p>Clientes</p>
              </a>
            </li>


            <li class="nav-item">
              <a href="motos.php" class="nav-link">
                <i class="nav-icon bi bi-scooter"></i>
                <p>Motos</p>
              </a>
            </li>


            <li class="nav-item">
              <a href="servicos.php" class="nav-link">
                <i class="nav-icon bi bi-tools"></i>
                <p>Serviços</p>
              </a>
            </li>


            <li class="nav-item">
              <a href="ordens.php" class="nav-link">
                <i class="nav-icon bi bi-clipboard-check"></i>
                <p>Ordens de Serviço</p>
              </a>
            </li>



            <li class="nav-item">
              <a href="financeiro.php" class="nav-link">
                <i class="nav-icon bi bi-cash-coin"></i>
                <p>Financeiro</p>
              </a>
            </li>





            <li class="nav-item">
              <a href="includes/logout.php" class="nav-link">
                <i class="nav-icon bi bi-box-arrow-right"></i>
                <p>Sair</p>
              </a>
            </li>
          </ul>
          <!--end::Sidebar Menu-->
        </nav>
      </div>
      <!--end::Sidebar Wrapper-->
    </aside>
    <!--end::Sidebar-->