<style>
/* Googlefont Poppins CDN Link */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap');
*{
  margin: 0;
  padding: 0;
  box-sizing: border-box ;
  font-family: 'Poppins', sans-serif !important;
}
.sidebar {
  position: fixed !important;
  height: 100% !important;
  width: 240px !important;
  background: #0A2558 !important;
  transition: all 0.5s ease !important;
  overflow-y: auto;
  overflow-x:auto; 
}

.sidebar::-webkit-scrollbar {
  width: 8px; 
  height: 8px;
}

.sidebar::-webkit-scrollbar-thumb {
  background: #ced4da;
  border-radius: 4px;
}

.sidebar::-webkit-scrollbar-track {
  background: transparent; 
}
.sidebar.active{
  width: 60px !important;
}
.sidebar .logo-details{
  height: 80px  !important;
  display: flex !important;
  align-items: center !important;
}
.sidebar .logo-details i{
  font-size: 28px !important;
  font-weight: 500 !important;
  color: #fff !important;
  min-width: 60px !important;
  text-align: center !important;
}
.sidebar .logo-details .logo_name{
  color: #fff !important;
  font-size: 24px !important;
  font-weight: 500 !important;
}
.sidebar .side-nav{
  margin-top: 10px !important;
  left:0 !important;
  padding:0 !important;
}
.sidebar .side-nav li{
  position: relative !important;
  list-style: none !important;
  height: 50px !important;
}
.sidebar .side-nav li a{
  height: 100% !important;
  width: 100% !important;
  display: flex !important;
  align-items: center !important;
  text-decoration: none !important;
  transition: all 0.4s ease !important;
}
.sidebar .side-nav li a.active{
  background: #081D45 !important;
}
.sidebar .side-nav li a:hover{
  background: #081D45 !important;
}
.sidebar .side-nav li i{
  min-width: 60px !important;
  text-align: center !important;
  font-size: 18px !important;
  color: #fff !important;
}
.sidebar .side-nav li a .links_name{
  color: #fff !important;
  font-size: 15px !important;
  font-weight: 400 !important;
  white-space: nowrap !important;
}
.sidebar .side-nav .log_out{
  position: absolute !important;
  bottom: 0 !important;
  width: 100% !important;
}
.home-section{
  position: relative !important;
  background: #f5f5f5 !important;
  min-height: 100vh !important;
  width: calc(100% - 240px) !important;
  left: 240px !important;
  transition: all 0.5s ease !important;
}
.sidebar.active ~ .home-section{
  width: calc(100% - 60px) !important;
  left: 60px !important;
}
.home-section .main-navigation{
  display: flex !important;
  justify-content: space-between !important;
  height: 80px !important;
  background: #fff !important;
  display: flex !important;
  align-items: center !important;
  position: fixed !important;
  width: calc(100% - 240px) !important;
  left: 240px !important;
  z-index: 100 !important;
  padding: 0 20px !important;
  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) !important;
  transition: all 0.5s ease !important;
}
.sidebar.active ~ .home-section nav{
  left: 60px !important;
  width: calc(100% - 60px) !important;
}
.home-section nav .sidebar-button{
  display: flex !important;
  align-items: center !important;
  font-size: 24px !important;
  font-weight: 500 !important;
}
nav .sidebar-button i{
  font-size: 35px !important;
  margin-right: 10px !important;
}

.home-section nav .profile-details{
  display: flex;
  align-items: center;
  background: #F5F6FA;

  border: 2px solid #EFEEF1;
  border-radius: 6px;
  height: 50px;
  min-width: 190px;
  padding: 0 15px 0 2px;
}
nav .profile-details img{
  height: 40px;
  width: 40px;
  border-radius: 6px;
  object-fit: cover;
}
nav .profile-details .admin_name{
  font-size: 15px;
  font-weight: 500;
  color: #333;
  margin: 0 10px;
  white-space: nowrap;
}
nav .profile-details i{
  font-size: 25px;
  color: #333;
}
.home-section .home-content{
  position: relative !important;
  padding-top: 104px !important;
}
.home-content .overview-boxes{
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  flex-wrap: wrap !important;
  padding: 0 20px !important;
  margin-bottom: 26px !important;
}
.overview-boxes .box{
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: calc(100% / 4 - 15px) !important;
  background: #fff;
  padding: 15px 14px;
  border-radius: 12px;
  box-shadow: 0 5px 10px rgba(0,0,0,0.1);
}
.overview-boxes .box-topic{
  font-size: 20px;
  font-weight: 500;
}
.number{
  display: inline-block;
  font-size: 35px;
  margin-top: -6px;
  font-weight: 500;
}
.home-content .box .indicator{
  display: flex;
  align-items: center;
}
.home-content .box .indicator i{
  height: 20px;
  width: 20px;
  background: #8FDACB;
  line-height: 20px;
  text-align: center;
  border-radius: 50%;
  color: #fff;
  font-size: 20px;
  margin-right: 5px;
}
.box .indicator i.down{
  background: #e87d88;
}
.home-content .box .indicator .text{
  font-size: 12px;
}
.cart{
  display: inline-block;
  font-size: 32px;
  height: 50px;
  width: 50px;
  background: #cce5ff;
  line-height: 50px;
  text-align: center;
  color: #66b0ff;
  border-radius: 12px;
  margin: -15px 0 0 6px;
}

.cart.four{
   color: #e05260;
   background: #f7d4d7;
 }
/* Hide the dropdown menus initially */
.dropdown-menu, .dropdown-submenu {
    display: none;
    position: absolute;
    list-style: none;
    margin: 0;
    padding: 0;
    background-color: #fff;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
}

/* Show the dropdown on hover */
.dropdown:hover .dropdown-menu {
    display: block;
}

.dropdown-menu li {
    position: relative;
}

.dropdown-submenu {
    left: 100%;
    top: 0;
}

/* Show the submenu on hover */
.dropdown-menu li:hover .dropdown-submenu {
    display: block;
}

.dropdown-menu a {
    display: block;
    padding: 10px;
    color: #333;
    text-decoration: none;
}

.dropdown-menu a:hover {
    background-color: #f1f1f1;
}

.custom-link{
            color: red; /* Change link color */
            text-decoration: none; /* Remove underline */
        }
.outer-wrapper {
            overflow: auto;
        }

        .table-wrapper {
            height: 650px; /* Set desired height */
            width: 100%;  /* Set desired width */
            overflow: auto;
        }

        table {
            width: 100%;
           
            border-collapse: separate;
            border-spacing: 0;
        }

        th, td {
            white-space: nowrap;
            padding: 8px 16px;
            border-bottom: 1px solid #dee2e6;
        }

        th {
           
            background: #f8f9fa;
            z-index: 2;
        }

        .table-wrapper::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #ced4da;
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #e9ecef;
            border-radius: 4px;
        }
/* Responsive Media Query */
@media (max-width: 1240px) {
  .sidebar{
    width: 60px;
  }
  .sidebar.active{
    width: 220px;
  }
  .home-section{
    width: calc(100% - 60px) !important;
    left: 60px !important;
  }
  .sidebar.active ~ .home-section{
    left: 220px !important;
    width: calc(100% - 220px) !important;
  }
  .home-section nav{
    width: calc(100% - 60px) !important;
    left: 60px !important;
  }
  .sidebar.active ~ .home-section nav{
    width: calc(100% - 220px) !important;
    left: 220px !important;
  }
}

@media (max-width: 400px) {
  .sidebar{
    width: 0;
  }
  .sidebar.active{
    width: 60px;
  }
  .home-section{
    width: 100% !important;
    left: 0 !important;
  }
  .sidebar.active ~ .home-section{
    left: 60px !important;
    width: calc(100% - 60px) !important;
  }
  .home-section nav{
    width: 100% !important;
    left: 60px !important;
  }
  .sidebar.active ~ .home-section nav{
    left: 60px !important;
    width: calc(100% - 60px) !important;
  }
}

.progress-bar-container {
    width: 100%;
    background-color:#e0e0e0;
    border-radius: 5px;
    overflow: hidden;
    height: 10px;
    margin: 10px 0;
}

.progress-bar {
    height: 100%;
    background-color: blue; /* Green color for the bar */
    text-align: center;
    color: white;
    line-height: 10px;
    font-weight: bold;
}

.form-control.bg-body.shadow-sm {
        border-color: #28a745; 
        border-radius:5px;
    }
.form-control.bg-body.shadow-sm:hover {
        border-color: #28a745; 
    }
</style>
