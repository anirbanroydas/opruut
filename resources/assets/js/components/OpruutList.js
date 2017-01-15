import React from 'react';
import classnames from 'classnames';

import OpruutItem from './OpruutItem';
import InfiniteScrollify from './InfiniteScrollify';
import Spinners from './Spinners';

import opruutListStyles from '../../sass/components/opruutList.scss';

let customFetchingStyle = {
	marginHeight: '10px'
}



class OpruutList extends React.Component {
	
	renderOpruutItems() {
		
		let opruuts = this.props.opruuts.map((opruut) => {
			
			return (
				<OpruutItem 
					key={opruut.id} 
					opruutId={opruut.id}
					isFavorited={opruut.isFavorited} 
					favorites_count={opruut.favorites_count}
					ride_time_tz={opruut.ride_time_tz}
					created_at_humans={opruut.created_at_humans}
					userName={opruut.userName} 
					userUsername={opruut.userUsername}
					avatar={opruut.userAvatar}
					from={opruut.from}
					to={opruut.to}
					preference={opruut.preference}
					city={opruut.city}
					cityImg={opruut.cityImg}
					routes={opruut.opruut_results}
					toggleFavorite={ (selectedOpruut, isFavorited) => this.props.toggleFavorite(selectedOpruut, isFavorited) }
					authenticated={ this.props.authenticated }
				/>
			);
		
		});

		return opruuts;

	}


	render() {

		return (

			<div className={ classnames(opruutListStyles.opruutListContainer) } >
				
				{ this.renderOpruutItems() }

				<div className={ classnames(opruutListStyles.padder) } style={{ height: '10px' }} />

				{ this.props.isFetching ? <Spinners type='wave' style={ customFetchingStyle } /> : null } 
				
			</div>
		);
	}

}


OpruutList.propTypes = {

	opruuts: React.PropTypes.array,
	isFetching: React.PropTypes.bool, 
	authenticated: React.PropTypes.bool,
	toggleFavorite: React.PropTypes.func,
	scrollFunc: React.PropTypes.func

};


export default InfiniteScrollify(OpruutList);

