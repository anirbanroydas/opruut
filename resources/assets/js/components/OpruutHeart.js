import React, { Component, PropTypes } from 'react';
import classnames from 'classnames';
import { Link } from 'react-router';

import IconButton from 'material-ui/IconButton';
import FavoriteIcon from 'material-ui/svg-icons/action/favorite';
import FavoriteBorderIcon from 'material-ui/svg-icons/action/favorite-border';
import {red500, yellow500, blue500} from 'material-ui/styles/colors';

import Popover from './Popover';

import opruutHeartStyles from '../../sass/components/opruutHeart.scss';
import popoverStyles from '../../sass/components/popover.scss';
import buttonStyles from '../../sass/components/buttons.scss';




let customIconButtonStyle = {
	border: "5px",
	padding: "0px",
	width: "30px",
	height: "30px",
	marginLeft: "7px"
}


class OpruutHeart extends Component {
	
	constructor(props) {
		super(props);
		
		this.toggleFavoriteAction = this.toggleFavoriteAction.bind(this);
		this.renderHearts = this.renderHearts.bind(this);
	}


	

	toggleFavoriteAction(e) {
		e.preventDefault();

		this.props.toggleFavorite(this.props.opruutId, this.props.isFavorited)
	}




	renderHearts() {
		let loves = this.props.favorites_count;

		if (!loves || loves === 0) {
			return 'Be the first one to love';
		}
		else {
			return `${loves} loves`
		}
	}




	render() {

		if (!this.props.authenticated) {
			return (
				<Popover favorites_count={this.props.favorites_count} className={ classnames(opruutHeartStyles.opruutHeart) } >
					
				</Popover>
			);
		}

		return (
			<div className={classnames(opruutHeartStyles.heartCountContainer)} >
				<IconButton onTouchTap={this.toggleFavoriteAction} style={customIconButtonStyle} >
			      	{ !this.props.isFavorited && <FavoriteBorderIcon /> }
			      	{ this.props.isFavorited && <FavoriteIcon color={red500} /> }
			    </IconButton>
			    <p className={classnames(opruutHeartStyles.heartCount)}> { this.renderHearts() } </p>
			</div>
		);
	}
}





OpruutHeart.propTypes = {

	opruutId: React.PropTypes.number,
	isFavorited: React.PropTypes.bool,
	favorites_count: React.PropTypes.number,
	authenticated: React.PropTypes.bool,
	toggleFavorite: React.PropTypes.func,

};



export default OpruutHeart;


