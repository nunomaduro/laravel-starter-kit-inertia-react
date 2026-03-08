import{j as e}from"./jsx-runtime-u17CrQMm.js";import{c as p}from"./utils-BQHNewu7.js";import{u as g}from"./use-reduced-motion-Bb6b8sk-.js";import{R as u,C as h}from"./chart-colors-wMYsAnQC.js";import{A as f,a as y}from"./AreaChart-DnA8_UZz.js";import{L as j,a as A}from"./LineChart-DJGfz-hY.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-BAEXrmqL.js";import"./getRadiusAndStrokeWidthFromDot-CHDRXA91.js";import"./CartesianChart-CkFwa3sR.js";import"./graphicalItemSelectors-CWGmRhZ6.js";import"./ErrorBarContext-X7aR2pnw.js";import"./ActiveShapeUtils-Caemlefb.js";function a({data:r,dataKey:d,variant:v="line",color:x,height:l=40,className:c}){const m=g(),i=x??h[0];return v==="area"?e.jsx("div",{className:p("w-full",c),style:{height:l},children:e.jsx(u,{width:"100%",height:"100%",children:e.jsx(f,{data:r,margin:{top:0,right:0,bottom:0,left:0},children:e.jsx(y,{type:"monotone",dataKey:d,stroke:i,fill:i,fillOpacity:.2,strokeWidth:1.5,dot:!1,isAnimationActive:!m})})})}):e.jsx("div",{className:p("w-full",c),style:{height:l},children:e.jsx(u,{width:"100%",height:"100%",children:e.jsx(j,{data:r,margin:{top:0,right:0,bottom:0,left:0},children:e.jsx(A,{type:"monotone",dataKey:d,stroke:i,strokeWidth:1.5,dot:!1,isAnimationActive:!m})})})})}a.__docgenInfo={description:"",methods:[],displayName:"Sparkline",props:{data:{required:!0,tsType:{name:"Array",elements:[{name:"Record",elements:[{name:"string"},{name:"unknown"}],raw:"Record<string, unknown>"}],raw:"Record<string, unknown>[]"},description:""},dataKey:{required:!0,tsType:{name:"string"},description:""},variant:{required:!1,tsType:{name:"union",raw:"'line' | 'area'",elements:[{name:"literal",value:"'line'"},{name:"literal",value:"'area'"}]},description:"",defaultValue:{value:"'line'",computed:!1}},color:{required:!1,tsType:{name:"string"},description:""},height:{required:!1,tsType:{name:"number"},description:"",defaultValue:{value:"40",computed:!1}},className:{required:!1,tsType:{name:"string"},description:""}}};const o=[{v:10},{v:25},{v:18},{v:40},{v:32},{v:55},{v:48},{v:62},{v:58},{v:75},{v:68},{v:90}],I={title:"Charts/Sparkline",component:a,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{variant:{control:"select",options:["line","area"]},height:{control:{type:"range",min:20,max:120,step:10}}}},t={args:{data:o,dataKey:"v",variant:"line",height:40},render:r=>e.jsx("div",{className:"w-40",children:e.jsx(a,{...r})})},s={args:{data:o,dataKey:"v",variant:"area",height:40},render:r=>e.jsx("div",{className:"w-40",children:e.jsx(a,{...r})})},n={render:()=>e.jsxs("div",{className:"flex items-center gap-6 rounded-lg border border-border bg-card p-4",children:[e.jsxs("div",{children:[e.jsx("p",{className:"text-xs text-muted-foreground",children:"Weekly Revenue"}),e.jsx("p",{className:"text-xl font-bold",children:"$12,450"}),e.jsx("p",{className:"text-xs text-success",children:"+8.2%"})]}),e.jsx("div",{className:"flex-1",children:e.jsx(a,{data:o,dataKey:"v",variant:"area",height:48})})]})};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    data: DATA,
    dataKey: 'v',
    variant: 'line',
    height: 40
  },
  render: args => <div className="w-40"><Sparkline {...args} /></div>
}`,...t.parameters?.docs?.source}}};s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  args: {
    data: DATA,
    dataKey: 'v',
    variant: 'area',
    height: 40
  },
  render: args => <div className="w-40"><Sparkline {...args} /></div>
}`,...s.parameters?.docs?.source}}};n.parameters={...n.parameters,docs:{...n.parameters?.docs,source:{originalSource:`{
  render: () => <div className="flex items-center gap-6 rounded-lg border border-border bg-card p-4">
            <div>
                <p className="text-xs text-muted-foreground">Weekly Revenue</p>
                <p className="text-xl font-bold">$12,450</p>
                <p className="text-xs text-success">+8.2%</p>
            </div>
            <div className="flex-1">
                <Sparkline data={DATA} dataKey="v" variant="area" height={48} />
            </div>
        </div>
}`,...n.parameters?.docs?.source}}};const E=["Line","Area","InContext"];export{s as Area,n as InContext,t as Line,E as __namedExportsOrder,I as default};
