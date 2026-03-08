import{j as r}from"./jsx-runtime-u17CrQMm.js";import{B as a}from"./bar-chart-D_Hpmrqw.js";import"./utils-BQHNewu7.js";import"./use-reduced-motion-Bb6b8sk-.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./skeleton-CuxgNOSS.js";import"./chart-colors-wMYsAnQC.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-BAEXrmqL.js";import"./CartesianChart-CkFwa3sR.js";import"./YAxis-C0ByQhTu.js";import"./getClassNameFromUnknown-wb-VDXTZ.js";import"./tooltipContext-BggRGtMu.js";import"./ActiveShapeUtils-Caemlefb.js";import"./ErrorBarContext-X7aR2pnw.js";import"./graphicalItemSelectors-CWGmRhZ6.js";const i=[{quarter:"Q1",sales:4200,returns:320},{quarter:"Q2",sales:5800,returns:410},{quarter:"Q3",sales:5100,returns:380},{quarter:"Q4",sales:7200,returns:520}],C={title:"Charts/BarChart",component:a,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{horizontal:{control:"boolean"},stacked:{control:"boolean"},showGrid:{control:"boolean"},showLegend:{control:"boolean"},skeleton:{control:"boolean"},height:{control:{type:"range",min:100,max:600,step:50}}}},s={args:{data:i,dataKeys:["sales"],xKey:"quarter",height:300},render:e=>r.jsx("div",{className:"w-[500px]",children:r.jsx(a,{...e})})},t={args:{data:i,dataKeys:["sales","returns"],xKey:"quarter",showLegend:!0,height:300},render:e=>r.jsx("div",{className:"w-[500px]",children:r.jsx(a,{...e})})},o={args:{data:i,dataKeys:["sales"],xKey:"quarter",horizontal:!0,height:300},render:e=>r.jsx("div",{className:"w-[500px]",children:r.jsx(a,{...e})})},d={args:{data:i,dataKeys:["sales","returns"],xKey:"quarter",stacked:!0,showLegend:!0,height:300},render:e=>r.jsx("div",{className:"w-[500px]",children:r.jsx(a,{...e})})};s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  args: {
    data: DATA,
    dataKeys: ['sales'],
    xKey: 'quarter',
    height: 300
  },
  render: args => <div className="w-[500px]"><BarChart {...args} /></div>
}`,...s.parameters?.docs?.source}}};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    data: DATA,
    dataKeys: ['sales', 'returns'],
    xKey: 'quarter',
    showLegend: true,
    height: 300
  },
  render: args => <div className="w-[500px]"><BarChart {...args} /></div>
}`,...t.parameters?.docs?.source}}};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  args: {
    data: DATA,
    dataKeys: ['sales'],
    xKey: 'quarter',
    horizontal: true,
    height: 300
  },
  render: args => <div className="w-[500px]"><BarChart {...args} /></div>
}`,...o.parameters?.docs?.source}}};d.parameters={...d.parameters,docs:{...d.parameters?.docs,source:{originalSource:`{
  args: {
    data: DATA,
    dataKeys: ['sales', 'returns'],
    xKey: 'quarter',
    stacked: true,
    showLegend: true,
    height: 300
  },
  render: args => <div className="w-[500px]"><BarChart {...args} /></div>
}`,...d.parameters?.docs?.source}}};const k=["Vertical","Grouped","Horizontal","Stacked"];export{t as Grouped,o as Horizontal,d as Stacked,s as Vertical,k as __namedExportsOrder,C as default};
