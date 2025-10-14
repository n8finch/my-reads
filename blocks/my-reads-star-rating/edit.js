import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { getRatingEmojis } from '../../includes/js/utils';

import './editor.scss';

export default function Edit({
  attributes: { rating, ratingStyle },
  setAttributes,
  context: { postType, postId },
}) {
  const [meta, updateMeta] = useEntityProp(
    'postType',
    postType,
    'meta',
    postId
  );

  useEffect(() => {
    const initStyle = meta?._myreads_ratingStyle
      ? meta?._myreads_ratingStyle
      : 'star';
    setAttributes({
      rating: meta?._myreads_rating || 0,
      ratingStyle: initStyle,
    });
  }, []);

  const onChangeRating = (val) => {
    updateMeta({
      ...meta,
      _myreads_rating: val,
    });
    setAttributes({ rating: val });
  };

  const onChangeRatingStyle = (val) => {
    updateMeta({
      ...meta,
      _myreads_ratingStyle: val,
    });
    setAttributes({ ratingStyle: val });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Rating', 'multiblock-plugin')}>
          <RangeControl
            label={__('Rating', 'multiblock-plugin')}
            value={rating}
            onChange={onChangeRating}
            min={0}
            max={5}
            step={0.5}
            help={
              0 === rating
                ? __('This will show as "Not yet rated".', 'multiblock-plugin')
                : ''
            }
          />
          <SelectControl
            label={__('Rating Style', 'multiblock-plugin')}
            onChange={onChangeRatingStyle}
            value={ratingStyle}
            options={[
              {
                label: __('Heart', 'multiblock-plugin'),
                value: 'heart',
              },
              {
                label: __('Star', 'multiblock-plugin'),
                value: 'star',
              },
            ]}
          />
        </PanelBody>
      </InspectorControls>
      <div {...useBlockProps()}>
        <p>
          Rating:{' '}
          <span className={`rating-${ratingStyle}`}>
            {' '}
            {getRatingEmojis(rating, ratingStyle)}
          </span>
        </p>
      </div>
    </>
  );
}
