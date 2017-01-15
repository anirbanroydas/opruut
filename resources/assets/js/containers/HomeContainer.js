import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import * as Actions from '../actions';
import Home from '../components/Home';



class HomeContainer extends React.Component {

	render() {
		
		return (
		         
            <Home { ...this.props } />
		);
	}

}






function mapStateToProps(state) {

	let { auth, opruuts, opruutRequest, search, livestream } = state;
	let { authenticated, userinfo, globalRequests } = auth;

	return {
		authenticated,
		userinfo,
		opruuts,
		globalRequests,
		opruutRequest,
		search,
		livestream
	};

}



function mapDispatchToProps(dispatch) {
	
	return {
		actions: bindActionCreators(Actions, dispatch)
	};
}



export default connect(mapStateToProps, mapDispatchToProps)(HomeContainer);





