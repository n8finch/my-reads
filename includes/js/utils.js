import { __ } from '@wordpress/i18n';

export const getRatingEmojis = (rating, ratingStyle) => {
  let ratingEmojis = '';

  // If rating is 0, return 'Not yet rated' message
  if (rating === 0) {
    return __('Not yet rated', 'my-reads');
  }

  // Append full emojis for each whole number in the rating.
  for (let i = 0.5; i < rating; i++) {
    ratingEmojis += ratingStyle === 'heart' ? '❤️' : '⭐';
  }
  // Append ½ if rating is half.
  if (rating % 1 !== 0) {
    ratingEmojis += '½';
  }

  return ratingEmojis;
};

export const getFormatAndEmoji = (format) => {
  // Make sure format is valid and return corresponding emoji and label
  const options = ['book', 'audiobook', 'comicbook', 'article'];
  if (!options.includes(format)) {
    return __('Other', 'my-reads'); // Default to 'book' if format is invalid
  }

  const formatOptions = {
    book: {
      icon: '📖',
      label: __('Book', 'my-reads'),
    },
    audiobook: {
      icon: '🎧',
      label: __('Audiobook', 'my-reads'),
    },
    comicbook: {
      icon: '🗯️',
      label: __('Comicbook', 'my-reads'),
    },
    article: {
      icon: '📰',
      label: __('Article', 'my-reads'),
    },
  };
  if (!format) {
    return formatOptions.book.icon + ' ' + formatOptions.book.label; // Default to 'book' if no format is set
  }
  return formatOptions[format]?.icon + ' ' + formatOptions[format]?.label;
};
