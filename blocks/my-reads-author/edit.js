import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { post } from '@wordpress/icons';

export default function Edit({ context: { postType, postId } }) {
  const [meta, setMeta] = useEntityProp('postType', postType, 'meta', postId);

  useEffect(() => {
    if (postType !== 'myreads') {
      return;
    }
    setMeta({
      ...meta,
      _myreads_author: meta?._myreads_author ? meta?._myreads_author : '',
    });
  }, []);

  const onChangeAuthor = (val) => {
    if (postType !== 'myreads') {
      return;
    }

    setMeta({
      ...meta,
      _myreads_author: val,
    });
  };

  return (
    <div {...useBlockProps()}>
      {'myreads' === postType ? (
        <p>
          <span>{__(' Author: ', 'my-reads')}</span>
          <RichText
            tagName='span'
            value={meta?._myreads_author ?? ''}
            allowedFormats={[]}
            onChange={onChangeAuthor}
            placeholder={__('Author...')}
          />
        </p>
      ) : (
        <p>
          {__(
            'This block is only interactive in My Reads posts, otherwise it is a placeholder.',
            'my-reads',
          )}
        </p>
      )}
    </div>
  );
}
