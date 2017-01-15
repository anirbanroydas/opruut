import React from 'react';
import { Link, browserHistory } from 'react-router';
import classnames from 'classnames';


import Avatar from 'material-ui/Avatar';
import navStyles from '../../sass/components/navbar.scss';



class Navbar extends React.Component {

	constructor(props) {
	  super(props);
	
	  this.handleSignout = this.handleSignout.bind(this);
	}


	handleSignout(event) {
		event.preventDefault();
		
		this.props.actions.signOutUser();
	}


	renderQuickLinkValue() {

		if (this.props.browserHistory.quicklink === '/') {
			return 'Favorites';
		}
		else if  (this.props.browserHistory.quicklink === '/favorites') {
			return 'Home';
		}
	}


	renderQuickLink() {

		if (this.props.browserHistory.quicklink === '/') {
			return '/favorites';
		}
		else if  (this.props.browserHistory.quicklink === '/favorites') {
			return '/';
		}
	}

	

	renderNavbarLinks() { 

		if (this.props.authenticated) {
			return [

				// <li className={ classnames('nav-item', navStyles.navItem) } key={1}>
				// 	<Link className={ classnames('nav-link', navStyles.navLink) } to="/">
				// 		<button type="submit" className={ classnames('btn', 'btn-default', 'btn-sm', 'favouritesbutton', 
				// 		navStyles.btn, navStyles.btnDefault, navStyles.btnSm, navStyles.favouritesbutton) } >
				// 			Home
				// 		</button>
				// 	</Link>
				// </li>,
			
				<li className={ classnames('nav-item', navStyles.navItem) } key={2}>
					<Link className={ classnames('nav-link', navStyles.navLink) } to={ this.renderQuickLink() }>
						<button type="submit" className={ classnames('btn btn-default btn-sm favouritesbutton', 
						navStyles.btn, navStyles.btnDefault, navStyles.btnSm, navStyles.favouritesbutton) }>
							{ this.renderQuickLinkValue() }
						</button>
					</Link>
				</li>,
				<li className={ classnames('nav-item', navStyles.navItem) } key={3}>
					<a className={ classnames('nav-link', navStyles.navLink) } href="/signout" onClick={this.handleSignout}>
						<button type="submit" className={ classnames('btn', 'btn-default', 'btn-sm', 'signoutbutton', 
						navStyles.btn, navStyles.btnDefault, navStyles.btnSm, navStyles.signoutbutton) }>
							Sign Out
						</button>
					</a>
				</li>,

		        <li className={ classnames('nav-item', navStyles.navItem) } key={4}>
	                <Link className={ classnames('menu-profile-name', navStyles.menuProfileName) } to="/">
	                	<p className={ classnames('menu-profile-text', navStyles.menuProfileText) }>{this.props.userinfo.name}</p>
	                	<Avatar 
							src={ this.props.userinfo.avatar? this.props.userinfo.avatar : "media/avatars/avatar_male.jpg" }
							size={33} 
							className={ classnames(navStyles.menuProfileImg) }  
						/>
	                </Link>
	            </li>
			];
		} 
		else {
			return [
				<li className={ classnames('nav-item', navStyles.navItem) } key={1}>
					<Link className={ classnames('nav-link', navStyles.navLink) } to="/login">
						<button type="submit" className={ classnames('btn', 'btn-default', 'btn-sm', 'loginbutton', 
						navStyles.btn, navStyles.btnDefault, navStyles.btnSm, navStyles.loginbutton) }>
							Log In
						</button>
					</Link>
				</li>,
				<li className={ classnames('nav-item', navStyles.navItem) } key={2}>
					<Link className={ classnames('nav-link', navStyles.navLink) } to="/register">
						<button type="submit" className={ classnames('btn', 'btn-default', 'btn-sm', 'signupbutton', 
						navStyles.btn, navStyles.btnDefault, navStyles.btnSm, navStyles.signupbutton) }>
							Sign Up
						</button>
					</Link>
				</li>
				
			];
		}
	}



	render() {

		return (

			<nav className={ classnames('navbar', 'navbar-default', 'navbar-fixed-top', 'menu-bar', navStyles.navbar, navStyles.navbarDefault,
				navStyles.navbarFixedTop, navStyles.menuBar) } >
	      		<div className={ classnames('container', 'header-container', navStyles.container, navStyles.headerContainer) } >
	        		<div className={ classnames('navbar-header', navStyles.navbarHeader) }>
	          			<button type="button" className={ classnames('navbar-toggle', 'collapsed', navStyles.navbarToggle, navStyles.collapsed) } data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				            <span className={ classnames('sr-only', navStyles.srOnly) } >Toggle navigation</span>
				            <span className={ classnames('icon-bar', navStyles.iconBar) } ></span>
				            <span className={ classnames('icon-bar', navStyles.iconBar) } ></span>
				            <span className={ classnames('icon-bar', navStyles.iconBar) } ></span>
	          			</button>
	          			<div className={ classnames('navbar-brand', navStyles.navbarBrand) }  href="/">
	          				<div className={ classnames('navbar-brand-name', navStyles.navbarBrandName) } >
		          				<Link className={ classnames('brand-img-link', navStyles.brandImgLink) }  to="/">
				                	<img className={ classnames('img-responsive',  'brand-img', navStyles.imgResponsive,  navStyles.brandImg) }  src="media/logoblack.png" alt="Brand_image" />
				                </Link>
	          					<Link className={ classnames('brand-text', navStyles.brandText) }  to="/">OpRuut</Link>		           
				            </div>         						
	          			</div>
	        		</div>
	        		<div id="navb" className={ classnames('navbar-collapse', 'collapse', navStyles.navbarCollapse, navStyles.collapse) } >
			          	<ul className={ classnames('nav', 'navbar-nav', 'navbar-right', navStyles.nav, navStyles.navbarNav, navStyles.navbarRight) } >
							{ this.renderNavbarLinks() }
						</ul>
					</div>
				</div>
			</nav>

		);
	}
}




Navbar.propTypes = {
	
	authenticated: React.PropTypes.bool,
	userinfo: React.PropTypes.object,
	browserHistory: React.PropTypes.object,
	actions: React.PropTypes.object

};



export default Navbar;



