-- point existing mysql product rows at the supplied webp photos
-- deploy the matching assets before applying this update

UPDATE products
SET
    image_path = CASE slug
        WHEN 'love-bunny' THEN '/assets/love-bunny.webp'
        WHEN 'bee' THEN '/assets/bee.webp'
        WHEN 'green-axolotl' THEN '/assets/green-axolotl.webp'
        WHEN 'dinosaur' THEN '/assets/dinosaur.webp'
        WHEN 'toad-style' THEN '/assets/toad-style.webp'
        WHEN 'flounder-style' THEN '/assets/flounder-style.webp'
        WHEN 'star' THEN '/assets/star.webp'
        WHEN 'tall-penguin' THEN '/assets/tall-penguin.webp'
        WHEN 'mushroom' THEN '/assets/mushroom.webp'
        WHEN 'bright-pink-christmas-loaf-cat' THEN '/assets/bright-pink-christmas-loaf-cat.webp'
        WHEN 'mini-love-bunny' THEN '/assets/mini-love-bunny.webp'
        WHEN 'octopus' THEN '/assets/octopus.webp'
        WHEN 'blue-penguin' THEN '/assets/blue-penguin.webp'
        WHEN 'green-penguin' THEN '/assets/green-penguin.webp'
        WHEN 'orange-penguin' THEN '/assets/orange-penguin.webp'
        WHEN 'purple-penguin' THEN '/assets/purple-penguin.webp'
        WHEN 'small-penguin' THEN '/assets/small-penguin.webp'
        WHEN 'small-black-and-grey-penguin' THEN '/assets/small-black-and-grey-penguin.webp'
        WHEN 'small-multicoloured-mushroom' THEN '/assets/small-multicoloured-mushroom.webp'
        WHEN 'small-yellow-mushroom' THEN '/assets/small-yellow-mushroom.webp'
        WHEN 'christmas-penguin' THEN '/assets/christmas-penguin.webp'
        WHEN 'christmas-tree' THEN '/assets/christmas-tree.webp'
        WHEN 'small-green-christmas-loaf-cat' THEN '/assets/small-green-christmas-loaf-cat.webp'
        WHEN 'small-pink-christmas-loaf-cat' THEN '/assets/small-pink-christmas-loaf-cat.webp'
        WHEN 'star-keyring' THEN '/assets/star-keyring.webp'
        WHEN 'glitter-green-scrunchie' THEN '/assets/glitter-green-scrunchie.webp'
        WHEN 'multicoloured-scrunchie' THEN '/assets/multicoloured-scrunchie.webp'
        WHEN 'pink-and-purple-scrunchie' THEN '/assets/pink-and-purple-scrunchie.webp'
        WHEN 'pink-scrunchie' THEN '/assets/pink-scrunchie.webp'
        WHEN 'harry-potter-style-coffee-holder' THEN '/assets/harry-potter-style-coffee-holder.webp'
        WHEN 'cat-nip-filled-toy' THEN '/assets/cat-nip-filled-toy.webp'
        WHEN 'christmas-nutcracker' THEN '/assets/christmas-nutcracker.webp'
        WHEN 'winter-bunny' THEN '/assets/winter-bunny.webp'
        ELSE image_path
    END,
    side_image_path = CASE slug
        WHEN 'love-bunny' THEN ''
        WHEN 'bee' THEN '/assets/bee-side-view.webp'
        WHEN 'green-axolotl' THEN '/assets/green-axolotl-side-view.webp'
        WHEN 'dinosaur' THEN '/assets/dinosaur-side-view.webp'
        WHEN 'toad-style' THEN '/assets/toad-style-side-view.webp'
        WHEN 'flounder-style' THEN '/assets/flounder-style-side-view.webp'
        WHEN 'star' THEN ''
        WHEN 'tall-penguin' THEN ''
        WHEN 'mushroom' THEN ''
        WHEN 'bright-pink-christmas-loaf-cat' THEN '/assets/bright-pink-christmas-loaf-cat-side-view.webp'
        WHEN 'mini-love-bunny' THEN ''
        WHEN 'octopus' THEN ''
        WHEN 'blue-penguin' THEN ''
        WHEN 'green-penguin' THEN ''
        WHEN 'orange-penguin' THEN ''
        WHEN 'purple-penguin' THEN ''
        WHEN 'small-penguin' THEN ''
        WHEN 'small-black-and-grey-penguin' THEN ''
        WHEN 'small-multicoloured-mushroom' THEN ''
        WHEN 'small-yellow-mushroom' THEN ''
        WHEN 'christmas-penguin' THEN ''
        WHEN 'christmas-tree' THEN ''
        WHEN 'small-green-christmas-loaf-cat' THEN '/assets/small-green-christmas-loaf-cat-side-view.webp'
        WHEN 'small-pink-christmas-loaf-cat' THEN '/assets/small-pink-christmas-loaf-cat-side-view.webp'
        WHEN 'star-keyring' THEN ''
        WHEN 'glitter-green-scrunchie' THEN ''
        WHEN 'multicoloured-scrunchie' THEN ''
        WHEN 'pink-and-purple-scrunchie' THEN ''
        WHEN 'pink-scrunchie' THEN ''
        WHEN 'harry-potter-style-coffee-holder' THEN ''
        WHEN 'cat-nip-filled-toy' THEN ''
        WHEN 'christmas-nutcracker' THEN '/assets/christmas-nutcracker-side-view.webp'
        WHEN 'winter-bunny' THEN ''
        ELSE side_image_path
    END
WHERE slug IN (
    'love-bunny',
    'bee',
    'green-axolotl',
    'dinosaur',
    'toad-style',
    'flounder-style',
    'star',
    'tall-penguin',
    'mushroom',
    'bright-pink-christmas-loaf-cat',
    'mini-love-bunny',
    'octopus',
    'blue-penguin',
    'green-penguin',
    'orange-penguin',
    'purple-penguin',
    'small-penguin',
    'small-black-and-grey-penguin',
    'small-multicoloured-mushroom',
    'small-yellow-mushroom',
    'christmas-penguin',
    'christmas-tree',
    'small-green-christmas-loaf-cat',
    'small-pink-christmas-loaf-cat',
    'star-keyring',
    'glitter-green-scrunchie',
    'multicoloured-scrunchie',
    'pink-and-purple-scrunchie',
    'pink-scrunchie',
    'harry-potter-style-coffee-holder',
    'cat-nip-filled-toy',
    'christmas-nutcracker',
    'winter-bunny'
);
