/**
 * @license Copyright (c) 2014-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
import { BalloonEditor } from '@ckeditor/ckeditor5-editor-balloon';
import { Autoformat } from '@ckeditor/ckeditor5-autoformat';
import { Bold, Italic } from '@ckeditor/ckeditor5-basic-styles';
import type { EditorConfig } from '@ckeditor/ckeditor5-core';
import { Essentials } from '@ckeditor/ckeditor5-essentials';
import { FontColor, FontSize } from '@ckeditor/ckeditor5-font';
import { Highlight } from '@ckeditor/ckeditor5-highlight';
import { Indent, IndentBlock } from '@ckeditor/ckeditor5-indent';
import { List } from '@ckeditor/ckeditor5-list';
import { Paragraph } from '@ckeditor/ckeditor5-paragraph';
import { Undo } from '@ckeditor/ckeditor5-undo';
import { WordCount } from '@ckeditor/ckeditor5-word-count';
declare class Editor extends BalloonEditor {
    static builtinPlugins: (typeof Autoformat | typeof Bold | typeof Essentials | typeof FontColor | typeof FontSize | typeof Highlight | typeof Indent | typeof IndentBlock | typeof Italic | typeof List | typeof Paragraph | typeof Undo | typeof WordCount)[];
    static defaultConfig: EditorConfig;
}
export default Editor;
