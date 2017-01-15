import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import * as AuthActionCreators from '../actions/AuthActions';
import Navbar from '../components/Navbar';




class NavbarContainer extends React.Component {
	
	render() {		
		
		return (
			<Navbar { ...this.props } />
		);
	}
}




function mapStateToProps(state) {	
	let { auth, browserHistory } = state;
	let { authenticated, userinfo } = auth;

	return {		
		authenticated,
		userinfo,
		browserHistory
	};
}



function mapDispatchToProps(dispatch) {
	return {
		actions: bindActionCreators(AuthActionCreators, dispatch)
	}
}



export default connect(mapStateToProps, mapDispatchToProps)(NavbarContainer);



