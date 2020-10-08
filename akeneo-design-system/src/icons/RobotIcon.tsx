import React from 'react';
import {IconProps} from './IconProps';

const RobotIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g fill="none" fillRule="evenodd">
      <path
        d="M15 8a5.5 5.5 0 014.93 3.059c.898.244 1.57 1.245 1.57 2.441 0 1.196-.672 2.197-1.57 2.442A5.5 5.5 0 0115 19H9a5.5 5.5 0 01-4.93-3.059c-.898-.244-1.57-1.245-1.57-2.441 0-1.196.672-2.197 1.57-2.442A5.5 5.5 0 019 8h6zm-6 2h6a3.5 3.5 0 010 7H9a3.5 3.5 0 010-7zm-.7 3.25c.427-.333.855-.5 1.282-.5.428 0 .855.167 1.282.5m2.436 0c.427-.333.855-.5 1.282-.5.428 0 .855.167 1.282.5"
        stroke={color}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <path
        d="M11.38 5.292c.07 0 .13.059.13.129v.258c0 .334.356.38.486.385h.031c.088 0 .52-.017.52-.385V5.42a.13.13 0 01.129-.129h.778c.07 0 .13.059.13.129v.258c0 .66-.422 1.173-1.056 1.35v.864c0 .088-.157.16-.349.16h-.34c-.191 0-.348-.072-.348-.16l-.001-.875c-.612-.186-1.018-.693-1.018-1.34v-.257a.13.13 0 01.13-.129h.778zM13.446 4c.07 0 .13.058.13.129v.773a.13.13 0 01-.13.129h-.778a.13.13 0 01-.13-.13V4.13a.13.13 0 01.13-.129h.778zm-2.074 0c.07 0 .13.058.13.129v.773a.13.13 0 01-.13.129h-.778a.13.13 0 01-.13-.13V4.13a.13.13 0 01.13-.129h.778z"
        fill={color}
      />
    </g>
  </svg>
);

export {RobotIcon};