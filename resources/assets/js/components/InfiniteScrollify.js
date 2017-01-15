import React, { Component, PropTypes } from 'react';

export default function (InnerComponent) {
	
	class InfiniteScrollComponent extends Component {
		
		constructor(props) {
			super(props);
			this.onScroll = this.onScroll.bind(this);
			this.scrollTop = this.scrollTop.bind(this);
			this.windowHeight = this.windowHeight.bind(this);
			this.documentContentHeight = this.documentContentHeight.bind(this);

		}

		componentDidMount() {
			window.addEventListener('scroll', this.onScroll, false);
		}

		componentWillUnmount() {
			window.removeEventListener('scroll', this.onScroll, false);
		}



		scrollTop() {
			let W = window;
			let D = document;
			return (W.pageYOffset !== undefined) ? W.pageYOffset : (D.body || D.documentElement || D.body.parentNode).scrollTop;
		}



		windowHeight() {
			let W = window;
			let D = document;
			if (W.innerHeight !== undefined || W.innerHeight !== null) {
				return W.innerHeight;
			}
			else {
				return Math.min(D.documentElement.clientHeight, D.documentElement.offsetHeight);
			}

		}



		documentContentHeight() {
			let D = document;
		    return Math.max(
		        D.body.scrollHeight, D.documentElement.scrollHeight,
		        D.body.offsetHeight, D.documentElement.offsetHeight,
		        D.body.clientHeight, D.documentElement.clientHeight
		    );
		}




		onScroll() {
			if (( this.windowHeight() + this.scrollTop()) >= ( this.documentContentHeight() - 200)) {
				// console.log('[INFINITE_SCROLLIFY] Scroll Function - scrolFunc - fetchOpruutIfNeeded/fetchFavoritesIfNeeded Action Called')
				this.props.scrollFunc(null, 'down');
			}
		}

		render() {
			return <InnerComponent { ...this.props } />;
		}
	}

	InfiniteScrollComponent.propTypes = {
		scrollFunc: PropTypes.func.isRequired,
	};

	return InfiniteScrollComponent;
}
