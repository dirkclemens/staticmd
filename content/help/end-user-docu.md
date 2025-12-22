---
Title: 0. End User Documentation
Visibility: public
---

# End User Documentation

Welcome to **StaticMD**, an easy-to-use content management system for creating websites with Markdown.  
This guide will help you get started and master all features.

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Using the Admin Interface](#using-the-admin-interface)
3. [Creating and Editing Content](#creating-and-editing-content)
4. [Markdown Basics](#markdown-basics)
5. [Advanced Features](#advanced-features)
6. [Managing Files](#managing-files)
7. [Customizing Your Site](#customizing-your-site)
8. [Search and Navigation](#search-and-navigation)
9. [Settings and Configuration](#settings-and-configuration)
10. [Tips and Best Practices](#tips-and-best-practices)

---

## Getting Started {#getting-started}

### Accessing the Admin Interface

1. Open your web browser
2. Navigate to your website URL followed by `/admin` (e.g., `https://yoursite.com/admin`)
3. Enter your username and password
4. Click "Login" to access the dashboard

### The Dashboard Overview

After logging in, you'll see the **Dashboard** with:

- **Statistics Cards**: Overview of your content (total pages, disk usage, file sizes)
- **Recent Files**: Quick access to your recently edited pages
- **Quick Actions**: Buttons to create new pages or browse files
- **Session Timer**: Shows how much time remains before automatic logout

---

## Using the Admin Interface {#using-the-admin-interface}

### Main Navigation

The sidebar on the left provides access to all main features:

- **Dashboard** (🏠): Your homepage in the admin area
- **Files** (📁): Browse and manage all your content files
- **New Page** (➕): Create a brand new page
- **Editor** (✏️): Open the content editor
- **Settings** (⚙️): Configure your website
- **View Site** (👁️): Preview your live website
- **Logout** (🚪): Sign out safely

### Session Management

- Your session timer is displayed in the top-right corner
- Shows remaining time before automatic logout (default: 24 hours)
- The system will warn you before your session expires
- You can extend your session by simply interacting with the admin interface

### Theme Toggle

- Click the moon/sun icon in the header to switch between light and dark mode
- Your preference is saved automatically
- This only affects the admin interface, not your public website

---

## Creating and Editing Content {#creating-and-editing-content}

### Creating a New Page

1. Click **"New Page"** in the sidebar or dashboard
2. Fill in the **File Route** (e.g., `about` or `blog/first-post`)
   - This becomes your page URL
   - Use lowercase letters, numbers, and hyphens - no umlauts
   - Folders are created automatically (e.g., `blog/post` creates a `blog` folder)
3. Add **Page Settings** in the left sidebar (most of them are *optional*):
   - **Title**: The page heading
   - **TitleSlug**: Short version for navigation
   - **Layout**: Choose from Standard, Wiki, Blog, Page, or Gallery
   - **Author**: Your name or username
   - **Tags**: Comma-separated keywords
   - **Visibility**: Public (everyone) or Private (admins only)
   - **Date**: Optional publication date
   - **Description**: Brief summary for search engines
4. Write your content in the main editor
5. Click **"Save"** to publish your page

### Using the Content Editor

The editor offers three view modes:

- **Editor**: Focus on writing
- **Preview**: See how your page will look
- **Split**: Edit and preview side-by-side with synchronized scrolling

#### Editor Toolbar

The toolbar provides quick access to formatting options:

**Text Formatting:**
- **B** - Bold text
- **I** - Italic text
- **S** - Strikethrough

**Headings:**
- **H1** - Main heading (largest)
- **H2** - Subheading
- **H3** - Sub-subheading
- **H4** - Smallest heading

**Lists:**
- **Bullets** - Unordered list
- **Numbers** - Ordered list
- **Checkboxes** - Task list

**Links & Media:**
- **Link** - Insert hyperlink
- **Image** - Insert image
- **Download** - Add downloadable file

**Code & Special:**
- **Code** - Inline code
- **Code Block** - Multi-line code section
- **Quote** - Blockquote
- **Table** - Insert table
- **HR** - Horizontal line
- **Bookmark** - Create jump anchor
- **Accordion** - Collapsible section
- **😊 Emoji** - Insert from 150+ emojis

**Additional Tools:**
- **Fullscreen** - Maximize editor
- **Syntax Check** - Validate your Markdown (a Markdown Linter)
- **Save** - Save your changes
- **Cancel** - Discard and return

#### Keyboard Shortcuts

**Basic Editing:**
- **Ctrl+Z** (Cmd+Z on Mac) - Undo
- **Ctrl+Y** or **Ctrl+Shift+Z** - Redo
- **Ctrl+A** (Cmd+A on Mac) - Select all
- **Ctrl+X** (Cmd+X on Mac) - Cut
- **Ctrl+C** (Cmd+C on Mac) - Copy
- **Ctrl+V** (Cmd+V on Mac) - Paste

**File Operations:**
- **Ctrl+S** (Cmd+S on Mac) - Save page
- **F11** or **Esc** - Toggle fullscreen

**Search & Replace:**
- **Ctrl+F** (Cmd+F on Mac) - Find in document
- **F3** or **Ctrl+G** - Find next
- **Shift+F3** or **Ctrl+Shift+G** - Find previous
- **Ctrl+H** (Cmd+Alt+F on Mac) - Replace

**Markdown Formatting:**
- **Ctrl+B** (Cmd+B on Mac) - Bold text
- **Ctrl+I** (Cmd+I on Mac) - Italic text
- **Ctrl+K** (Cmd+K on Mac) - Inline code
- **Ctrl+L** (Cmd+L on Mac) - Insert link

**Line Operations:**
- **Ctrl+D** - Delete line
- **Ctrl+Shift+D** - Duplicate line
- **Alt+Up** - Move line up
- **Alt+Down** - Move line down
- **Ctrl+/** (Cmd+/ on Mac) - Toggle comment

**Indentation:**
- **Tab** - Increase indent
- **Shift+Tab** - Decrease indent
- **Ctrl+[** (Cmd+Alt+5 on Mac) - Decrease indent
- **Ctrl+]** (Cmd+Alt+6 on Mac) - Increase indent

**Advanced:**
- **Ctrl+Space** - Autocomplete
- **Ctrl+K Ctrl+U** - Convert to uppercase
- **Ctrl+K Ctrl+L** - Convert to lowercase

### Drag & Drop Uploads

You can drag files directly into the editor:

**Images** (JPG, PNG, GIF, WebP):
- Drag from your computer into the editor
- Image is uploaded automatically
- Markdown code is inserted: `[image filename.jpg "Description" - 50%]` with "n%" as value for image size 

**Documents** (PDF, ZIP):
- Drag PDF or ZIP files into the editor
- File is uploaded to `/public/downloads/`
- Download tag is inserted: `[download filename.pdf "Description"]`

---

## Markdown Basics {#markdown-basics}

Markdown is a simple way to format text using plain text symbols.

### Text Formatting

```markdown
**Bold text**
*Italic text*
~~Strikethrough~~
`Inline code`
```

### Headings

```markdown
# Heading 1 (Main Title)
## Heading 2 (Section)
### Heading 3 (Subsection)
#### Heading 4 (Sub-subsection)
```

### Links

```markdown
[Link text](https://example.com)
[Link to another page](/about)
```

### Images

```markdown
![Image description](/path/to/image.jpg)

Or using StaticMD syntax with size:
[image photo.jpg "Description" - 50%]
```

### Lists

**Unordered (Bullets):**
```markdown
- First item
- Second item
     - Sub second item (with 4 indents)
          - Sub-sub second item (with 2 time 4 indents)
- Third item
```

**Ordered (Numbers):**
```markdown
1. First step
2. Second step
    - Sub second step (with 4 indents)
3. Third step
```

**Task Lists:**
```markdown
- [ ] Todo item
- [x] Completed item
```

### Code

**Inline code:**
```markdown
Use the `printf()` function
```

**Code blocks:**    
Use 3 backticks at the begin and end of the code block
```markdown
   ```javascript
   function hello() {
   console.log("Hello World!");
   }
   ```
```

### Quotes

```markdown
> This is a quote
> It can span multiple lines
```

### Tables

```markdown
| Column 1 | Column 2 | Column 3 |
|----------|----------|----------|
| Row 1    | Data     | Data     |
| Row 2    | Data     | Data     |
```

### Horizontal Lines

```markdown
---
```

---

## Advanced Features {#advanced-features}

### Emojis

Use GitHub-style emoji codes anywhere in your text:

```markdown
I :heart: StaticMD! :rocket: :smile:
```

**Popular emojis:**
- `:smile:` 😄 `:grin:` 😁 `:joy:` 😂 `:blush:` 😊
- `:heart:` ❤️ `:blue_heart:` 💙 `:broken_heart:` 💔
- `:thumbsup:` 👍 `:thumbsdown:` 👎 `:clap:` 👏
- `:fire:` 🔥 `:star:` ⭐ `:rocket:` 🚀 `:tada:` 🎉
- `:computer:` 💻 `:phone:` 📱 `:bulb:` 💡

Access the full emoji picker via the 😊 button in the editor toolbar.

### Custom Header IDs

Create custom anchor links for headings:

```markdown
# My Section {#custom-id}

Link to it: [Jump to section](#custom-id)
```

### Jump Anchors

Create bookmarks anywhere in your page:

```markdown
[Click here to jump](#bookmark-name)

{#bookmark-name}
This is where readers will land
```

### Shortcodes

StaticMD provides powerful shortcodes for dynamic content:

#### Pages Listing

Display a list of pages from a folder:

```markdown
[pages /blog/ 10]
```
- Shows 10 most recent pages from `/blog/` folder
- Includes titles, dates, and descriptions
- Automatically formatted

#### Tag Cloud

Show all tags used in a folder:

```markdown
[tags /blog/ 20]
```
- Displays up to 20 most-used tags
- badge numbers reflect tag frequency
- Tags are clickable

#### Folder Listing

List all files in a specific folder:

```markdown
[folder /documentation/ 50]
```

#### Image Gallery

Create an automatic image gallery:

```markdown
[gallery my-photos]
```
- Loads all images from `/public/images/my-photos/`
- Beautiful lightbox viewer
- Automatic thumbnails
- Tag filtering support

#### Download Links

Provide downloadable files:

```markdown
[download manual.pdf "User Manual"]
[download archive.zip "Download Archive"]
```
- Automatic file icons (📄 for PDF, 📦 for ZIP)
- Files must be in `/public/downloads/`

#### Accordions (Collapsible Sections)

[accordionstart section1 "Click to expand"]
Lorem ipsum dolor sit amet, consectetur adipiscing elit ...
[accordionstop section1]

Create expandable content sections:
```markdown
[accordionstart section1 "Click to expand"]
Lorem ipsum dolor sit amet, consectetur adipiscing elit ...
[accordionstop section1]

[accordionstart section2 "Another section"]
More hidden content here
[accordionstop section2]
```

### Gallery Layout

Create a dedicated gallery page:

1. Set **Layout: gallery** in page settings
2. Upload images to `/public/images/your-gallery/`
3. Add the gallery shortcode:
```markdown
[gallery your-gallery]
```

**Optional: Tag your images** by adding tags to the alt text:
```markdown
![Nature photo nature landscape](/images/gallery/photo.jpg)
```

Tags enable filtering in the gallery view.

### Secret content

Hide content from visitors (admin only readable)

[authstart]
This text is visible for logged in users only
[authstop]

```markdown
[authstart]
This is protected content
[authstop]
```

**With custom message:**
```markdown
[authstart message="Please login to view this content"]
This is protected content
[authstop]
```

---

## Managing Files {#managing-files}

### File Manager

Access via **Files** in the sidebar to:

- **Browse** all your content in a tree structure
- **Search** for specific files
- **View** file details (size, modification date, route)
- **Rename/Move** files using the inline form
- **Delete** files (with confirmation)

### File Organization

Files are organized in folders that mirror your URL structure:

```
/content/
  ├── index.md           → Homepage (/)
  ├── about.md           → /about
  ├── blog/
  │   ├── index.md       → /blog (folder overview)
  │   └── first-post.md  → /blog/first-post
  └── documentation/
      └── guide.md       → /documentation/guide
```

### Index Files

Create `index.md` in any folder to:
- Provide a landing page for that section
- Show custom content instead of automatic file listing
- Control the folder's appearance in navigation

### Viewing and Editing

From the file manager, you can:
- **👁️ View** - Open the page on your public website
- **✏️ Edit** - Open in the editor
- **🗑️ Delete** - Remove the file (with confirmation)
- **↔️ Rename/Move** - Change filename or move to another folder

---

## Customizing Your Site {#customizing-your-site}

### Choosing a Theme

1. Go to **Settings** in the sidebar
2. Scroll to **Frontend Theme**
3. Select from 9 available Bootstrap 5 based themes:
    - **Bootstrap** - Clean, professional default
    - **Solarized Light** - Gentle, easy on the eyes
    - **Solarized Dark** - Dark theme for low-light
    - **Monokai Light** - Bright, code-friendly
    - **Monokai Dark** - Dark code-friendly theme
    - **GitHub Light** - Familiar GitHub style
    - **GitHub Dark** - GitHub dark mode
    - **Static-MD** - Custom StaticMD theme
    - and some more ...
4. Click **Save Settings**
5. Visit your site to see the new theme

### Website Name and Logo

1. Open **Settings**
2. Under **Website**:
    - **Website Name** - Appears in navigation and page titles
    - **Logo URL** - Link to your logo image
3. Click **Save Settings**

### Language Selection

Change the admin interface language:
1. Go to **Settings**
2. Select **Language**: English or Deutsch (German)
3. Save your changes

### Navigation Order

Control how pages appear in your main navigation:

1. Go to **Settings** → **Navigation**
2. Drag items in the **Navigation Order** list
3. Higher positions appear first in menus
4. Click **Save Settings**

**Navigation Dropdowns:**
- Enable/disable dropdown menus for folders
- When enabled, folders show their contents in a dropdown
- When disabled, clicking folders navigates to the folder's index page

---

## Search and Navigation {#search-and-navigation}

### Using Search

**For Visitors:**
1. Type in the search box on your website
2. Press Enter or click Search
3. Results show with:
    - Page title (clickable)
    - Excerpt with search terms highlighted
    - Page path/location
    - Relevance ranking

**Search Tips:**
- Search covers titles, content, tags, and descriptions
- Multiple words search for all terms
- Results are ranked by relevance
- Tag filtering available via tag clouds

**For Admins:**
1. Use the Files page search box
2. Instantly filters your file list
3. Searches filenames and paths

### Search Result Limit

Control how many results appear:
1. Go to **Settings** → **Dashboard**
2. Adjust **Search Result Limit** (10-200)
3. Default: 50 results per search

### Navigation Features

Your site automatically includes:
- **Main navigation** - Top-level pages and folders
- **Breadcrumbs** - Show current location path
- **Tag clouds** - Browse content by category
- **Folder overviews** - Automatic listings when no index page exists

---

## Settings and Configuration {#settings-and-configuration}

### Dashboard Settings

**Recent Files Count** (5-50):
- How many files appear on the dashboard
- Default: 10 files

**Show File Statistics**:
- Toggle detailed file statistics on dashboard
- Includes size, modification dates, etc.

### Editor Settings

**Editor Theme** - Choose from 7 code editor themes:
- **Elegant** - Light, clean design
- **Eclipse** - Java IDE style
- **Idea** - JetBrains style
- **Monokai** - Dark, high contrast
- **Solarized Light** - Gentle light theme
- **Solarized Dark** - Gentle dark theme
- **Material** - Google Material dark

**Auto-Save Interval** (30-300 seconds):
- How often the editor auto-saves drafts
- Set to 30s for frequent saving
- Set to 300s (5 min) for less frequent saves

### SEO Settings

**Robots Policy** - Control search engine indexing:
- **Index, Follow** - Allow search engines (recommended)
- **Index, No Follow** - Index page but don't follow links
- **No Index, Follow** - Don't index but follow links
- **No Index, No Follow** - Completely hide from search engines

**Block Crawlers**:
- Blocks all search engines entirely
- Use for development/private sites only
- Adds strict robots.txt rules

**Generate robots.txt**:
- Creates automatic `/robots.txt` file
- Updates based on your SEO settings
- View at `yoursite.com/robots.txt`

### Per-Page SEO

Add to your page's front matter:

```markdown
---
Title: My Page
Robots: noindex,nofollow
Description: This page is hidden from search engines
Canonical: https://example.com/original-page
---
```

### Backup System

Create complete backups of your site:

1. Go to **Settings** → **Backup & Restore**
2. Review what will be backed up:
    - All content files (`/content/`)
    - Configuration (`config.php`, `settings.json`)
    - Themes (`/system/themes/`)
    - Uploaded files (`/public/images/`, `/public/downloads/`)
3. Click **"Create Backup"**
4. Download starts automatically
5. Save the ZIP file to a safe location

**Backup includes:**
- Total file count
- Total size estimate
- Creation timestamp in filename

---

## Tips and Best Practices {#tips-and-best-practices}

### Content Writing

**Do:**
- ✅ Use descriptive filenames: `getting-started` not `page1`
- ✅ Add titles and descriptions to all pages
- ✅ Use tags to organize related content
- ✅ Break long pages into sections with headings
- ✅ Add alt text to all images
- ✅ Test links regularly

**Don't:**
- ❌ Use special characters in filenames (stick to lowercase, hyphens)
- ❌ Create very long URLs (keep routes short)
- ❌ Forget to save your work
- ❌ Delete files without checking for links

### Organization

**Folder Structure:**
```
/content/
  ├── index.md                    # Homepage
  ├── about.md                    # About page
  ├── contact.md                  # Contact
  ├── blog/                       # Blog section
  │   ├── index.md                # Blog landing
  │   ├── 2024-11-01-post.md      # Blog posts with dates
  │   └── 2024-11-15-news.md
  └── documentation/              # Docs section
      ├── index.md                # Docs home
      ├── getting-started.md      # Guide
      └── advanced/               # Sub-section
          └── features.md
```

### Performance

**Images:**
- Optimize images before uploading (compress, resize)
- Use appropriate sizes (don't upload 5MB photos)
- Consider using 50-80% width for images, not 100%
- Add descriptive alt text for accessibility

**Content:**
- Break very long pages into multiple pages
- Use accordions for lengthy sections
- Link between related pages
- Keep navigation to 10-15 items max

### Security

**Passwords:**
- Use strong admin passwords
- Change default credentials immediately
- Don't share admin access

**Visibility:**
- Use `Visibility: private` for draft/admin-only content
- Use `Robots: noindex` for pages you don't want in search engines
- Review public content regularly

### Workflow

**Regular Tasks:**
1. **Weekly**: Review recent files, check for broken links
2. **Monthly**: Create backup, review analytics/search terms
3. **Quarterly**: Update themes, review and archive old content
4. **As Needed**: Update navigation order, create new sections

**Before Publishing:**
1. Preview your page
2. Check all links work
3. Verify images display correctly
4. Test on mobile if possible
5. Review spelling and grammar
6. Add appropriate tags

---

## Frequently Asked Questions

### How do I change my password?

Currently, passwords are configured in the system files. Contact your system administrator to change your admin password.

### Can I use HTML in my Markdown?

Yes! You can mix HTML and Markdown. Markdown won't process text inside HTML tags, so you can use HTML for advanced formatting when needed.

### How do I add a contact form?

StaticMD focuses on content pages. For interactive features like forms, consider embedding third-party solutions or using HTML with external form services.

### Can multiple people edit at once?

Only one admin session at a time is recommended. Changes are saved immediately, so simultaneous editing could cause conflicts.

### What if I accidentally delete something?

If you have a recent backup, you can restore deleted files from there. This is why regular backups (Settings → Backup) are important!

### How do I add a new page to navigation?

New pages appear automatically in navigation. Control the order in Settings → Navigation by dragging items to your preferred position.

### Why isn't my page showing up?

Check:
- Is `Visibility` set to "public"?
- Did you save the page?
- Is the filename valid (lowercase, no spaces)?
- Try logging out and viewing as a visitor

### How do I create a blog?

1. Create a `/blog/` folder
2. Add `blog/index.md` for your blog homepage
3. Create posts as `blog/post-name.md`
4. Use the `[pages /blog/ 10]` shortcode on your index to list you first 10 posts
5. Add dates and tags to posts for organization

### Can visitors download files?

Yes! Upload files to `/public/downloads/` and use:
```markdown
[download filename.pdf "Download Description"]
```

### How do I make a multi-language site?

Create separate folders for each language:
```
/content/
  ├── en/
  │   └── index.md
  └── de/
      └── index.md
```

Add language switcher links manually on pages.

---

## Getting Help

### In-App Resources

- **Keyboard Shortcuts**: Click your username → Keyboard Shortcuts
- **Syntax Check**: Use the "Syntax Check" button in the editor
- **Preview**: Always preview before saving
- **Session Timer**: Monitor in top-right corner

### Best Practices

- **Save frequently** - Use Ctrl+S often
- **Test changes** - Preview before publishing
- **Create backups** - Before major changes
- **Use descriptive names** - For files and folders
- **Organize with tags** - For better search and navigation
- **Check links** - After moving or renaming files

### Shortcuts Summary

| Action | Shortcut |
|--------|----------|
| Save page | Ctrl+S (Cmd+S) |
| Bold text | Ctrl+B |
| Italic text | Ctrl+I |
| Inline code | Ctrl+K |
| Insert link | Ctrl+L |
| Find in page | Ctrl+F |
| Fullscreen | F11 or Esc |
| Undo | Ctrl+Z |
| Redo | Ctrl+Y |

---

## Conclusion

**StaticMD** makes creating and managing content simple and enjoyable. With its powerful Markdown editor, beautiful themes, and comprehensive features, you can build professional websites without technical complexity.

**Remember:**
- 📝 Write in simple Markdown
- 🎨 Choose from 9 beautiful themes
- 📁 Organize with folders and tags
- 🔍 Let visitors search your content
- 💾 Backup regularly
- 🚀 Publish with confidence

---

*This documentation covers StaticMD as a content management system. For technical setup, server configuration, or development questions, please refer to the technical documentation or contact your system administrator.*