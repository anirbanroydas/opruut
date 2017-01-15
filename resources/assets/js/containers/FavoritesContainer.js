import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import * as Actions from '../actions';
import Favorites from '../components/Favorites';



class FavoritesContainer extends React.Component {

	render() {
		
		return (
		         
            <Favorites { ...this.props } />
		);
	}

}






function mapStateToProps(state) {

	let { auth, favorites } = state; 
	let { authenticated, userinfo, globalRequests } = auth;

	return {
		authenticated,
		userinfo,
		favorites,
		globalRequests
	};

}



function mapDispatchToProps(dispatch) {
	
	return {
		actions: bindActionCreators(Actions, dispatch)
	};
}



export default connect(mapStateToProps, mapDispatchToProps)(FavoritesContainer);





