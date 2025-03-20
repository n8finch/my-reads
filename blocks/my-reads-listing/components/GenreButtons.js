import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const GenreButtons = ({ posts, onFilterChange }) => {
  // Compute counts per genre
  const genreCounts = posts.reduce((counts, post) => {
    if (Array.isArray(post.genres)) {
      post.genres.forEach((genre) => {
        counts[genre] = (counts[genre] || 0) + 1;
      });
    }
    return counts;
  }, {});

  // Total count for "All"
  const allCount = posts.length;

  // Assuming posts have a boolean "favorite" property.
  // If not, you can modify this to suit your favorites logic.
  const favoritesCount = posts.filter((post) => post._my_reads_isFavorite).length;

  // Convert genres to an array for rendering
  const genreButtons = Object.entries(genreCounts).map(([genre, count]) => ({
    genre,
    count,
  }));

  // State to keep track of the active filter; default is 'All'
  const [activeGenre, setActiveGenre] = useState('All');

  const handleClick = (genre) => {
    setActiveGenre(genre);
    onFilterChange(genre); // Pass the selected genre (or 'All'/'Favorites') up to parent.
  };

  return (
    <div className='genre-buttons'>
      <button
        className={`wp-element-button ${activeGenre === 'All' ? 'active' : ''}`}
        onClick={() => handleClick('All')}>
        {__('All', 'my-reads')} ({allCount})
      </button>

      <button
        className={`wp-element-button ${activeGenre === 'Favorites' ? 'active' : ''}`}
        onClick={() => handleClick('Favorites')}>
        {__('Favorites', 'my-reads')} ({favoritesCount})
      </button>

      {genreButtons.map(({ genre, count }) => (
        <button
          key={genre}
          className={`wp-element-button ${activeGenre === genre ? 'active' : ''}`}
          onClick={() => handleClick(genre)}>
          {__(genre, 'my-reads')} ({count})
        </button>
      ))}
    </div>
  );
};

export default GenreButtons;
