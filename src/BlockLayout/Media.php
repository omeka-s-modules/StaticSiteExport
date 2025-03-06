<?php
namespace StaticSiteExport\BlockLayout;

use ArrayObject;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Job\JobInterface;

class Media implements BlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        SitePageBlockRepresentation $block,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $attachments = $block->attachments();
        if (!$attachments) {
            return '';
        }

        $layout = $block->dataValue('layout');
        $mediaDisplay = $block->dataValue('media_display');
        $thumbnailType = $block->dataValue('thumbnail_type', 'square');
        $showTitleOption = $block->dataValue('show_title_option', 'item_title');

        $classes = ['media-embed'];
        if ('horizontal' === $layout) {
            $classes[] = 'layout-horizontal';
        } else {
            $classes[] = 'layout-vertical';
        }
        if ('thumbnail' === $mediaDisplay) {
            $classes[] = 'media-display-thumbnail';
        } else {
            $classes[] = 'media-display-embed';
        }
        if (3 < count($attachments)) {
            $classes[] = 'multiple-attachments';
        } else {
            $classes[] = 'attachment-count-' . count($attachments);
        }

        $markdown = [];
        foreach($attachments as $attachment) {
            $item = $attachment->item();
            $media = $attachment->media() ?: $item->primaryMedia();
            // @todo: Finish cribbing off of file.phtml
        }

        return implode("\n", $markdown);
    }
}
