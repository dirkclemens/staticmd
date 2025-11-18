---
Title: 8. Gallery Layout - Guide
Author: System
Layout: Standard
Tag: gallery, layout, guide, documentation
Description: Complete guide for the Gallery Layout in StaticMD
---

# Gallery Layout - Complete Guide

The Gallery Layout is a specialized template for displaying image galleries with modern features like lightbox, responsive design, and filter options.

## Activation

To use the Gallery Layout, add `Layout: gallery` to the front matter of your Markdown file:

```markdown
---
Title: My Gallery
Layout: gallery
Author: Your Name
Tag: nature, landscape, images
Description: A beautiful collection of images
---
```

## Front Matter Options

### Required
- `Layout: gallery` - Activates the Gallery Layout

### Optional
- `Tag: tag1, tag2, tag3` - Enables image filtering
- `Description: ...` - Displayed in the gallery header
- `author: Name` - Shown in gallery statistics
- `date: YYYY-MM-DD` - Shown in gallery statistics

## Adding Images

### 1. Local Images (recommended)

**Step 1:** Upload images to `/public/assets/galleries/`

> **Important:** Files in `/public/assets/galleries/` are accessed via `/assets/galleries/...` URLs due to the asset routing system.
```bash
# Example structure:
/public/assets/galleries/
                ├── gallery1/
                │   ├── image1.jpg
                │   ├── image2.jpg
                │   └── image3.jpg
                └── portfolio/
                    ├── project1.png
                    └── project2.png
```

**Step 2:** Include images in Markdown
```markdown
![Paris, Galeries Lafayette](/assets/galleries/paris/1_20110223_073728.jpg "Paris, Galeries Lafayette")

![Description](/assets/galleries/gallery1/image1.jpg "Nature")
![Another Image](/assets/galleries/gallery1/image2.jpg "Landscape")

# Real example with Paris gallery:
![Paris](/assets/galleries/paris/1_20110223_073728.jpg "Paris")

# Test multiple images:
![Paris Street](/assets/galleries/paris/2010_0516_121252.jpg "Paris Street")
![Paris Architecture](/assets/galleries/paris/2010_0516_155733.jpg "Paris Architecture")
```

### 2. External Images

```markdown
![External Image](https://example.com/image.jpg "Description")
```

> **Note:** External images may be blocked by Content Security Policy (CSP).

## Features

### 🖼️ Automatic Gallery Creation
- **Manual Method:** All images manually added to Markdown are automatically converted into a responsive grid
- **Auto-Load Method:** Use `[gallery]` shortcode to automatically load all images from a directory
- Hover effects with info overlays
- Variable image heights for masonry layout

#### Auto-Gallery Shortcode
```markdown
[gallery DIRECTORY LIMIT]

# Examples:
[gallery paris]                          # Load all images from /public/assets/galleries/paris/
[gallery paris 20]                       # Limit to 20 images
[gallery /assets/galleries/hdr/ 15]      # Absolute path with limit
```

### 🔍 Lightbox Functionality
- Click on images for fullscreen view
- Navigate between images with arrow keys
- Touch navigation on mobile devices
- Automatic captions from alt text

### 🏷️ Filter System
When tags are defined in the front matter, filter buttons are automatically created:

```markdown
---
Tag: nature, architecture, portrait
---

![Tree](/assets/galleries/gallery1/tree.jpg "Nature")
![Building](/assets/galleries/gallery1/building.jpg "Architecture")
![Person](/assets/galleries/gallery1/person.jpg "Portrait")
```

Images can then be filtered by tags.

### 📊 Automatic Statistics
- Image count is automatically calculated
- Display of author and date
- Count updates when filtering

## Responsive Design

### Desktop (>= 1200px)
- 4-column grid
- Masonry layout with variable heights
- Large hover effects

### Tablet (768px - 1199px)
- 3-column grid
- Smaller hover effects
- Touch-optimized navigation

### Mobile (< 768px)
- 1-column layout
- Touch gestures for lightbox
- Simplified navigation

## Advanced Features

### Custom CSS
You can add custom CSS in the front matter:

```markdown
---
Layout: gallery
css: |
  .gallery-item img {
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  }
---
```

### Custom JavaScript
For extended functionality:

```markdown
---
Layout: gallery
js: |
  // Additional gallery functions
  console.log('Gallery loaded!');
---
```

## Example Gallery

Here is a complete example:

```markdown
---
Title: My Nature Photography
Layout: gallery
Author: John Doe
Date: 2024-11-16
Tag: nature, landscape, photography
Description: A collection of my best nature shots from 2024
---

# Nature Photography 2024

This gallery showcases my favorite nature subjects from this year.

## Spring
![Cherry Blossom](/assets/galleries/nature/cherry-blossom.jpg "Spring")
![Forest Path](/assets/galleries/nature/forest-path.jpg "Spring")

## Summer
![Sunflower Field](/assets/galleries/nature/sunflower-field.jpg "Summer")
![Mountain Lake](/assets/galleries/nature/mountain-lake.jpg "Summer")

## Autumn
![Autumn Forest](/assets/galleries/nature/autumn-forest.jpg "Autumn")
![Morning Fog](/assets/galleries/nature/morning-fog.jpg "Autumn")
```

## Troubleshooting

### Images not displaying
1. **Check path:** Make sure the image path is correct
2. **Permissions:** Check file permissions
3. **CSP:** External images might be blocked by Content Security Policy

### Lightbox not working
1. **JavaScript:** Check browser console for errors
2. **CDN:** Ensure GLightbox CDN links are reachable

### Filter buttons not appearing
1. **Tags:** Check if tags are defined in front matter
2. **Format:** Tags must be comma-separated: `Tag: tag1, tag2, tag3`

## Best Practices

### 🎯 Image Optimization
- **Format:** JPEG for photos, PNG for graphics
- **Size:** Maximum 1920px width for web
- **Compression:** Balance between quality and file size

### 📝 Alt Text
- Descriptive alt text for accessibility
- Alt text is used as caption in lightbox
- Can be used for filtering

### 🏷️ Tag Strategy
- Consistent tag naming
- Not too many tags per gallery (max. 5-7)
- Use logical categories
