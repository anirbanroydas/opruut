import React from 'react';
import { browserHistory } from 'react-router';
import classnames from 'classnames';

import ProfileBarLarge from './ProfileBarLarge';
import OpruutList from './OpruutList';

import favoritesStyles from '../../sass/components/favorites.scss';






class Favorites extends React.Component {
	
	componentWillMount() {
		// console.log('Favorites : Component will mount');
		this.props.actions.updateNavbarQuickLink();
		this.props.actions.refreshInvalidDataIfNeeded();
	}


	componentDidMount() {
		// console.log('Favorites Component Did Mount');
		this.setScrollPosition();
		this.props.actions.fetchFavoritesFirstTimeIfNeeded();
	}



	setScrollPosition() {
		// console.log('Favorites : setScrollPosition');
		let D = document;
		let currentScrollTop = (D.body || D.documentElement || D.body.parentNode).scrollTop;
		D.body.scrollTop = currentScrollTop  - 400;
		if (D.documentElement) {
			D.documentElement.scrollTop = currentScrollTop - 400;
		}
		let finatScrollTop = (D.body || D.documentElement || D.body.parentNode).scrollTop;
		// console.log('Favorites : scrolltop : ', finatScrollTop);
	}


	render() {
		
		return (

			<div className={ classnames('container', favoritesStyles.homeContainer) } >
				<div className={ classnames('row') } >							
					
					<div className={ classnames('col-md-2', 'hidden-xs', 'hidden-sm', favoritesStyles.leftContentWrapper) } >
					</div>

					<div className={ classnames('col-xs-12', 'col-md-8', favoritesStyles.centerContentWrapper) } >
						<div className={ classnames('center-block', favoritesStyles.opruutRequestBarContainer) } >									
							
							<ProfileBarLarge  
								authenticated={ this.props.authenticated } 
								userinfo={ this.props.userinfo }
								globalRequests={ this.props.globalRequests }
							/>

						</div>

						<div className={ classnames('center-block', favoritesStyles.opruutListContainer) } >	

							<OpruutList 
								opruuts={ this.props.favorites.data }  
								isFetching={ this.props.favorites.isFetching }
								toggleFavorite={ selectedOpruut => this.props.actions.toggleFavorite(selectedOpruut) }
								authenticated={ this.props.authenticated }
								scrollFunc={ (requestData, fetch_type) => this.props.actions.fetchFavoritesIfNeeded(requestData, fetch_type) }
							/>
												
						</div>
													
					</div>
					
				</div>
			</div>
		
		);
	}
}







Favorites.propTypes = {
	
	authenticated: React.PropTypes.bool,
	userinfo: React.PropTypes.oneOfType([
	    React.PropTypes.oneOf([null]),
	    React.PropTypes.object
	]),
	actions: React.PropTypes.object,
	favorites: React.PropTypes.object,
	globalRequests: React.PropTypes.number
};






export default Favorites;



